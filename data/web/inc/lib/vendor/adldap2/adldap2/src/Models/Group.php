<?php

namespace Adldap\Models;

use Adldap\Utilities;
use InvalidArgumentException;

/**
 * Class Group.
 *
 * Represents an LDAP group (security / distribution).
 */
class Group extends Entry
{
    use Concerns\HasMemberOf;
    use Concerns\HasDescription;

    /**
     * Returns all users apart of the current group.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms677097(v=vs.85).aspx
     *
     * @return \Adldap\Query\Collection
     */
    public function getMembers()
    {
        $members = $this->getMembersFromAttribute($this->schema->member());

        if (count($members) === 0) {
            $members = $this->getPaginatedMembers();
        }

        return $this->newCollection($members);
    }

    /**
     * Returns the group's member names only.
     *
     * @return array
     */
    public function getMemberNames()
    {
        $members = [];

        $dns = $this->getAttribute($this->schema->member()) ?: [];

        foreach ($dns as $dn) {
            $exploded = Utilities::explodeDn($dn);

            if (array_key_exists(0, $exploded)) {
                $members[] = $exploded[0];
            }
        }

        return $members;
    }

    /**
     * Sets the groups members using an array of user DNs.
     *
     * @param array $entries
     *
     * @return $this
     */
    public function setMembers(array $entries)
    {
        return $this->setAttribute($this->schema->member(), $entries);
    }

    /**
     * Adds multiple entries to the current group.
     *
     * @param array $members
     *
     * @return bool
     */
    public function addMembers(array $members)
    {
        $members = array_map(function ($member) {
            return $member instanceof Model
                ? $member->getDn()
                : $member;
        }, $members);

        $mod = $this->newBatchModification(
            $this->schema->member(),
            LDAP_MODIFY_BATCH_ADD,
            $members
        );

        return $this->addModification($mod)->save();
    }

    /**
     * Adds an entry to the current group.
     *
     * @param string|Entry $member
     *
     * @throws InvalidArgumentException When the given entry is empty or contains no distinguished name.
     *
     * @return bool
     */
    public function addMember($member)
    {
        $member = ($member instanceof Model ? $member->getDn() : $member);

        if (is_null($member)) {
            throw new InvalidArgumentException(
                'Cannot add member to group. The members distinguished name cannot be null.'
            );
        }

        $mod = $this->newBatchModification(
            $this->schema->member(),
            LDAP_MODIFY_BATCH_ADD,
            [$member]
        );

        return $this->addModification($mod)->save();
    }

    /**
     * Removes an entry from the current group.
     *
     * @param string|Entry $member
     *
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    public function removeMember($member)
    {
        $member = ($member instanceof Model ? $member->getDn() : $member);

        if (is_null($member)) {
            throw new InvalidArgumentException(
                'Cannot remove member to group. The members distinguished name cannot be null.'
            );
        }

        $mod = $this->newBatchModification(
            $this->schema->member(),
            LDAP_MODIFY_BATCH_REMOVE,
            [$member]
        );

        return $this->addModification($mod)->save();
    }

    /**
     * Removes all members from the current group.
     *
     * @return bool
     */
    public function removeMembers()
    {
        $mod = $this->newBatchModification(
            $this->schema->member(),
            LDAP_MODIFY_BATCH_REMOVE_ALL
        );

        return $this->addModification($mod)->save();
    }

    /**
     * Returns the group type integer.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms675935(v=vs.85).aspx
     *
     * @return string
     */
    public function getGroupType()
    {
        return $this->getFirstAttribute($this->schema->groupType());
    }

    /**
     * Retrieves group members by the specified model attribute.
     *
     * @param $attribute
     *
     * @return array
     */
    protected function getMembersFromAttribute($attribute)
    {
        $members = [];

        $entries = $this->getAttribute($attribute) ?: [];

        $query = $this->query->newInstance();

        // Retrieving the member identifier to allow
        // compatibility with LDAP variants.
        $identifier = $this->schema->memberIdentifier();

        foreach ($entries as $entry) {
            // If our identifier is a distinguished name, then we need to
            // use an alternate query method, as we can't locate records
            // by distinguished names using an LDAP filter.
            if ($identifier == 'dn' || $identifier == 'distinguishedname') {
                $member = $query->findByDn($entry);
            } else {
                // We'll ensure we clear our filters when retrieving each member,
                // so we can continue fetching the next one in line.
                $member = $query->clearFilters()->findBy($identifier, $entry);
            }

            // We'll double check that we've received a model from
            // our query before adding it into our results.
            if ($member instanceof Model) {
                $members[] = $member;
            }
        }

        return $members;
    }

    /**
     * Retrieves members that are contained in a member range.
     *
     * @return array
     */
    protected function getPaginatedMembers()
    {
        $members = [];

        $keys = array_keys($this->attributes);

        // We need to filter out the model attributes so
        // we only retrieve the member range.
        $attributes = array_values(array_filter($keys, function ($key) {
            return strpos($key, 'member;range') !== false;
        }));

        // We'll grab the member range key so we can run a
        // regex on it to determine the range.
        $key = reset($attributes);

        preg_match_all(
            '/member;range\=([0-9]{1,4})-([0-9*]{1,4})/',
            $key,
            $matches
        );

        if ($key && count($matches) == 3) {
            // Retrieve the ending range number.
            $to = $matches[2][0];

            // Retrieve the current groups members from the
            // current range string (ex. 'member;0-50').
            $members = $this->getMembersFromAttribute($key);

            // If the query already included all member results (indicated
            // by the '*'), then we can return here. Otherwise we need
            // to continue on and retrieve the rest.
            if ($to === '*') {
                return $members;
            }

            // Determine the amount of members we're requesting per query.
            $range = $to - $matches[1][0];

            // Set our starting range to our last end range plus one.
            $from = $to + 1;

            // We'll determine the new end range by adding the
            // total range to our new starting range.
            $to = $from + $range;

            // We'll need to query for the current model again but with
            // a new range to retrieve the other members.
            /** @var Group $group */
            $group = $this->query->newInstance()->findByDn(
                $this->getDn(),
                [$this->query->getSchema()->memberRange($from, $to)]
            );

            // Finally, we'll merge our current members
            // with the newly returned members.
            $members = array_merge(
                $members,
                $group->getMembers()->toArray()
            );
        }

        return $members;
    }
}
