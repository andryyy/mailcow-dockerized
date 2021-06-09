<?php

namespace LdapRecord\Query\Model;

use Closure;
use LdapRecord\LdapInterface;
use LdapRecord\Models\Attributes\AccountControl;
use LdapRecord\Models\ModelNotFoundException;

class ActiveDirectoryBuilder extends Builder
{
    /**
     * Finds a record by its Object SID.
     *
     * @param string       $sid
     * @param array|string $columns
     *
     * @return \LdapRecord\Models\ActiveDirectory\Entry|static|null
     */
    public function findBySid($sid, $columns = [])
    {
        try {
            return $this->findBySidOrFail($sid, $columns);
        } catch (ModelNotFoundException $e) {
            return;
        }
    }

    /**
     * Finds a record by its Object SID.
     *
     * Fails upon no records returned.
     *
     * @param string       $sid
     * @param array|string $columns
     *
     * @return \LdapRecord\Models\ActiveDirectory\Entry|static
     *
     * @throws ModelNotFoundException
     */
    public function findBySidOrFail($sid, $columns = [])
    {
        return $this->findByOrFail('objectsid', $sid, $columns);
    }

    /**
     * Adds a enabled filter to the current query.
     *
     * @return $this
     */
    public function whereEnabled()
    {
        return $this->notFilter(function ($query) {
            return $query->whereDisabled();
        });
    }

    /**
     * Adds a disabled filter to the current query.
     *
     * @return $this
     */
    public function whereDisabled()
    {
        return $this->rawFilter(
            (new AccountControl())->accountIsDisabled()->filter()
        );
    }

    /**
     * Adds a 'where member' filter to the current query.
     *
     * @param string $dn
     * @param bool   $nested
     *
     * @return $this
     */
    public function whereMember($dn, $nested = false)
    {
        return $this->nestedMatchQuery(function ($attribute) use ($dn) {
            return $this->whereEquals($attribute, $dn);
        }, 'member', $nested);
    }

    /**
     * Adds an 'or where member' filter to the current query.
     *
     * @param string $dn
     * @param bool   $nested
     *
     * @return $this
     */
    public function orWhereMember($dn, $nested = false)
    {
        return $this->nestedMatchQuery(function ($attribute) use ($dn) {
            return $this->orWhereEquals($attribute, $dn);
        }, 'member', $nested);
    }

    /**
     * Adds a 'where member of' filter to the current query.
     *
     * @param string $dn
     * @param bool   $nested
     *
     * @return $this
     */
    public function whereMemberOf($dn, $nested = false)
    {
        return $this->nestedMatchQuery(function ($attribute) use ($dn) {
            return $this->whereEquals($attribute, $dn);
        }, 'memberof', $nested);
    }

    /**
     * Adds a 'where not member of' filter to the current query.
     *
     * @param string $dn
     * @param bool   $nested
     *
     * @return $this
     */
    public function whereNotMemberof($dn, $nested = false)
    {
        return $this->nestedMatchQuery(function ($attribute) use ($dn) {
            return $this->whereNotEquals($attribute, $dn);
        }, 'memberof', $nested);
    }

    /**
     * Adds an 'or where member of' filter to the current query.
     *
     * @param string $dn
     * @param bool   $nested
     *
     * @return $this
     */
    public function orWhereMemberOf($dn, $nested = false)
    {
        return $this->nestedMatchQuery(function ($attribute) use ($dn) {
            return $this->orWhereEquals($attribute, $dn);
        }, 'memberof', $nested);
    }

    /**
     * Adds a 'or where not member of' filter to the current query.
     *
     * @param string $dn
     * @param bool   $nested
     *
     * @return $this
     */
    public function orWhereNotMemberof($dn, $nested = false)
    {
        return $this->nestedMatchQuery(function ($attribute) use ($dn) {
            return $this->orWhereNotEquals($attribute, $dn);
        }, 'memberof', $nested);
    }

    /**
     * Adds a 'where manager' filter to the current query.
     *
     * @param string $dn
     * @param bool   $nested
     *
     * @return $this
     */
    public function whereManager($dn, $nested = false)
    {
        return $this->nestedMatchQuery(function ($attribute) use ($dn) {
            return $this->whereEquals($attribute, $dn);
        }, 'manager', $nested);
    }

    /**
     * Adds a 'where not manager' filter to the current query.
     *
     * @param string $dn
     * @param bool   $nested
     *
     * @return $this
     */
    public function whereNotManager($dn, $nested = false)
    {
        return $this->nestedMatchQuery(function ($attribute) use ($dn) {
            return $this->whereNotEquals($attribute, $dn);
        }, 'manager', $nested);
    }

    /**
     * Adds an 'or where manager' filter to the current query.
     *
     * @param string $dn
     * @param bool   $nested
     *
     * @return $this
     */
    public function orWhereManager($dn, $nested = false)
    {
        return $this->nestedMatchQuery(function ($attribute) use ($dn) {
            return $this->orWhereEquals($attribute, $dn);
        }, 'manager', $nested);
    }

    /**
     * Adds an 'or where not manager' filter to the current query.
     *
     * @param string $dn
     * @param bool   $nested
     *
     * @return $this
     */
    public function orWhereNotManager($dn, $nested = false)
    {
        return $this->nestedMatchQuery(function ($attribute) use ($dn) {
            return $this->orWhereNotEquals($attribute, $dn);
        }, 'manager', $nested);
    }

    /**
     * Execute the callback with a nested match attribute.
     *
     * @param Closure $callback
     * @param string  $attribute
     * @param bool    $nested
     *
     * @return $this
     */
    protected function nestedMatchQuery(Closure $callback, $attribute, $nested = false)
    {
        return $callback(
            $nested ? $this->makeNestedMatchAttribute($attribute) : $attribute
        );
    }

    /**
     * Make a "nested match" filter attribute for querying descendants.
     *
     * @param string $attribute
     *
     * @return string
     */
    protected function makeNestedMatchAttribute($attribute)
    {
        return sprintf('%s:%s:', $attribute, LdapInterface::OID_MATCHING_RULE_IN_CHAIN);
    }
}
