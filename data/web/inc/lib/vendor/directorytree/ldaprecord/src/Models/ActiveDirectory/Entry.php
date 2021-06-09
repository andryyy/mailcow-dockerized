<?php

namespace LdapRecord\Models\ActiveDirectory;

use InvalidArgumentException;
use LdapRecord\Connection;
use LdapRecord\Models\Attributes\Sid;
use LdapRecord\Models\Entry as BaseEntry;
use LdapRecord\Models\Events\Updated;
use LdapRecord\Models\Types\ActiveDirectory;
use LdapRecord\Query\Model\ActiveDirectoryBuilder;

/** @mixin ActiveDirectoryBuilder */
class Entry extends BaseEntry implements ActiveDirectory
{
    /**
     * The default attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $defaultDates = [
        'whenchanged'           => 'windows',
        'whencreated'           => 'windows',
        'dscorepropagationdata' => 'windows',
    ];

    /**
     * The attribute key that contains the Object SID.
     *
     * @var string
     */
    protected $sidKey = 'objectsid';

    /**
     * @inheritdoc
     */
    public function getObjectSidKey()
    {
        return $this->sidKey;
    }

    /**
     * @inheritdoc
     */
    public function getObjectSid()
    {
        return $this->getFirstAttribute($this->sidKey);
    }

    /**
     * @inheritdoc
     */
    public function getConvertedSid()
    {
        try {
            return (string) new Sid($this->getObjectSid());
        } catch (InvalidArgumentException $e) {
            return;
        }
    }

    /**
     * Create a new query builder.
     *
     * @param Connection $connection
     *
     * @return ActiveDirectoryBuilder
     */
    public function newQueryBuilder(Connection $connection)
    {
        return new ActiveDirectoryBuilder($connection);
    }

    /**
     * Determine if the object is deleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        return strtoupper($this->getFirstAttribute('isDeleted')) === 'TRUE';
    }

    /**
     * Restore a deleted object.
     *
     * @param string|null $newParentDn
     *
     * @return bool
     *
     * @throws \LdapRecord\LdapRecordException
     */
    public function restore($newParentDn = null)
    {
        if (! $this->isDeleted()) {
            return false;
        }

        $root = $newParentDn ?? $this->getDefaultRestoreLocation();
        $rdn = explode('\0A', $this->getDn(), 2)[0];
        $newDn = implode(',', [$rdn, $root]);

        // We will initialize a model listener for the "updated" event to set
        // the models distinguished name so all attributes are synchronized
        // properly after the model has been successfully restored.
        $this->listenForModelEvent(Updated::class, function (Updated $event) use ($newDn) {
            if ($this->is($event->getModel())) {
                $this->setDn($newDn);
            }
        });

        $this->save([
            'isDeleted'         => null,
            'distinguishedName' => $newDn,
        ]);
    }

    /**
     * Get the RootDSE (AD schema) record from the directory.
     *
     * @param string|null $connection
     *
     * @return static
     *
     * @throws \LdapRecord\Models\ModelNotFoundException
     */
    public static function getRootDse($connection = null)
    {
        return static::on($connection ?? (new static)->getConnectionName())
            ->in(null)
            ->read()
            ->whereHas('objectclass')
            ->firstOrFail();
    }

    /**
     * Get the objects restore location.
     *
     * @return string
     */
    protected function getDefaultRestoreLocation()
    {
        return $this->getFirstAttribute('lastKnownParent') ?? $this->getParentDn($this->getParentDn($this->getDn()));
    }

    /**
     * Converts attributes for JSON serialization.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function convertAttributesForJson(array $attributes = [])
    {
        $attributes = parent::convertAttributesForJson($attributes);

        if ($this->hasAttribute($this->sidKey)) {
            // If the model has a SID set, we need to convert it due to it being in
            // binary. Otherwise we will receive a JSON serialization exception.
            return array_replace($attributes, [
                $this->sidKey => [$this->getConvertedSid()],
            ]);
        }

        return $attributes;
    }
}
