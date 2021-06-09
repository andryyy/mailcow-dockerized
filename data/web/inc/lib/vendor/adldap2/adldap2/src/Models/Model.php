<?php

namespace Adldap\Models;

use DateTime;
use ArrayAccess;
use Adldap\Utilities;
use JsonSerializable;
use Adldap\Query\Builder;
use Illuminate\Support\Arr;
use Adldap\Query\Collection;
use InvalidArgumentException;
use UnexpectedValueException;
use Adldap\Models\Attributes\Sid;
use Adldap\Models\Attributes\Guid;
use Adldap\Schemas\SchemaInterface;
use Adldap\Models\Attributes\MbString;
use Adldap\Connections\ConnectionException;
use Adldap\Models\Attributes\DistinguishedName;

/**
 * Class Model.
 *
 * Represents an LDAP record and provides the ability
 * to modify / retrieve data from the record.
 */
abstract class Model implements ArrayAccess, JsonSerializable
{
    use Concerns\HasEvents;
    use Concerns\HasAttributes;

    /**
     * Indicates if the model exists.
     *
     * @var bool
     */
    public $exists = false;

    /**
     * The current query builder instance.
     *
     * @var Builder
     */
    protected $query;

    /**
     * The current LDAP attribute schema.
     *
     * @var SchemaInterface
     */
    protected $schema;

    /**
     * Contains the models modifications.
     *
     * @var array
     */
    protected $modifications = [];

    /**
     * Constructor.
     *
     * @param array   $attributes
     * @param Builder $builder
     */
    public function __construct(array $attributes, Builder $builder)
    {
        $this->setQuery($builder)
            ->setSchema($builder->getSchema())
            ->fill($attributes);
    }

    /**
     * Returns the models distinguished name when the model is converted to a string.
     *
     * @return null|string
     */
    public function __toString()
    {
        return $this->getDn();
    }

    /**
     * Sets the current query builder.
     *
     * @param Builder $builder
     *
     * @return $this
     */
    public function setQuery(Builder $builder)
    {
        $this->query = $builder;

        return $this;
    }

    /**
     * Returns the current query builder.
     *
     * @return Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Returns a new query builder instance.
     *
     * @return Builder
     */
    public function newQuery()
    {
        return $this->query->newInstance();
    }

    /**
     * Returns a new batch modification.
     *
     * @param string|null     $attribute
     * @param string|int|null $type
     * @param array           $values
     *
     * @return BatchModification
     */
    public function newBatchModification($attribute = null, $type = null, $values = [])
    {
        return new BatchModification($attribute, $type, $values);
    }

    /**
     * Returns a new collection with the specified items.
     *
     * @param mixed $items
     *
     * @return Collection
     */
    public function newCollection($items = [])
    {
        return new Collection($items);
    }

    /**
     * Sets the current model schema.
     *
     * @param SchemaInterface $schema
     *
     * @return $this
     */
    public function setSchema(SchemaInterface $schema)
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * Returns the current model schema.
     *
     * @return SchemaInterface
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Determine if the given offset exists.
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return !is_null($this->getAttribute($offset));
    }

    /**
     * Get the value for a given offset.
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param string $offset
     * @param mixed  $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value at the given offset.
     *
     * @param string $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $attributes = $this->getAttributes();

        array_walk_recursive($attributes, function (&$val) {
            if (MbString::isLoaded()) {
                // If we're able to detect the attribute
                // encoding, we'll encode only the
                // attributes that need to be.
                if (!MbString::isUtf8($val)) {
                    $val = utf8_encode($val);
                }
            } else {
                // If the mbstring extension is not loaded, we'll
                // encode all attributes to make sure
                // they are encoded properly.
                $val = utf8_encode($val);
            }
        });

        // We'll replace the binary GUID and SID with
        // their string equivalents for convenience.
        return array_replace($attributes, [
            $this->schema->objectGuid() => $this->getConvertedGuid(),
            $this->schema->objectSid()  => $this->getConvertedSid(),
        ]);
    }

    /**
     * Reload a fresh model instance from the directory.
     *
     * @return static|null
     */
    public function fresh()
    {
        $model = $this->query->newInstance()->findByDn($this->getDn());

        return $model instanceof self ? $model : null;
    }

    /**
     * Synchronizes the current models attributes with the directory values.
     *
     * @return bool
     */
    public function syncRaw()
    {
        if ($model = $this->fresh()) {
            $this->setRawAttributes($model->getAttributes());

            return true;
        }

        return false;
    }

    /**
     * Returns the models batch modifications to be processed.
     *
     * @return array
     */
    public function getModifications()
    {
        $this->buildModificationsFromDirty();

        return $this->modifications;
    }

    /**
     * Sets the models modifications array.
     *
     * @param array $modifications
     *
     * @return $this
     */
    public function setModifications(array $modifications = [])
    {
        $this->modifications = $modifications;

        return $this;
    }

    /**
     * Adds a batch modification to the models modifications array.
     *
     * @param array|BatchModification $mod
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function addModification($mod = [])
    {
        if ($mod instanceof BatchModification) {
            $mod = $mod->get();
        }

        if ($this->isValidModification($mod)) {
            $this->modifications[] = $mod;

            return $this;
        }

        throw new InvalidArgumentException(
            "The batch modification array does not include the mandatory 'attrib' or 'modtype' keys."
        );
    }

    /**
     * Returns the model's distinguished name string.
     *
     * @link https://msdn.microsoft.com/en-us/library/aa366101(v=vs.85).aspx
     *
     * @return string|null
     */
    public function getDistinguishedName()
    {
        return $this->getFirstAttribute($this->schema->distinguishedName());
    }

    /**
     * Sets the model's distinguished name attribute.
     *
     * @param string|DistinguishedName $dn
     *
     * @return $this
     */
    public function setDistinguishedName($dn)
    {
        $this->setFirstAttribute($this->schema->distinguishedName(), (string) $dn);

        return $this;
    }

    /**
     * Returns the model's distinguished name string.
     *
     * (Alias for getDistinguishedName())
     *
     * @link https://msdn.microsoft.com/en-us/library/aa366101(v=vs.85).aspx
     *
     * @return string|null
     */
    public function getDn()
    {
        return $this->getDistinguishedName();
    }

    /**
     * Returns a DistinguishedName object for modifying the current models DN.
     *
     * @return DistinguishedName
     */
    public function getDnBuilder()
    {
        // If we currently don't have a distinguished name, we'll set
        // it to our base, otherwise we'll use our query's base DN.
        $dn = $this->getDistinguishedName() ?: $this->query->getDn();

        return $this->getNewDnBuilder($dn);
    }

    /**
     * Returns the models distinguished name components.
     *
     * @param bool $removeAttributePrefixes
     *
     * @return array
     */
    public function getDnComponents($removeAttributePrefixes = true)
    {
        if ($components = Utilities::explodeDn($this->getDn(), $removeAttributePrefixes)) {
            unset($components['count']);

            return $components;
        }

        return [];
    }

    /**
     * Returns the distinguished name that the model is a leaf of.
     *
     * @return string
     */
    public function getDnRoot()
    {
        $components = $this->getDnComponents(false);

        // Shift off the beginning of the array;
        // This contains the models RDN.
        array_shift($components);

        return implode(',', $components);
    }

    /**
     * Returns a new DistinguishedName object for building onto.
     *
     * @param string $baseDn
     *
     * @return DistinguishedName
     */
    public function getNewDnBuilder($baseDn = '')
    {
        return new DistinguishedName($baseDn);
    }

    /**
     *  Sets the model's distinguished name attribute.
     *
     * (Alias for setDistinguishedName())
     *
     * @param string $dn
     *
     * @return $this
     */
    public function setDn($dn)
    {
        return $this->setDistinguishedName($dn);
    }

    /**
     * Returns the model's hex object SID.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679024(v=vs.85).aspx
     *
     * @return string
     */
    public function getObjectSid()
    {
        return $this->getFirstAttribute($this->schema->objectSid());
    }

    /**
     * Returns the model's binary object GUID.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679021(v=vs.85).aspx
     *
     * @return string
     */
    public function getObjectGuid()
    {
        return $this->getFirstAttribute($this->schema->objectGuid());
    }

    /**
     * Returns the model's GUID.
     *
     * @return string|null
     */
    public function getConvertedGuid()
    {
        try {
            return (string) new Guid($this->getObjectGuid());
        } catch (InvalidArgumentException $e) {
            return;
        }
    }

    /**
     * Returns the model's SID.
     *
     * @return string|null
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
     * Returns the model's common name.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms675449(v=vs.85).aspx
     *
     * @return string
     */
    public function getCommonName()
    {
        return $this->getFirstAttribute($this->schema->commonName());
    }

    /**
     * Sets the model's common name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setCommonName($name)
    {
        return $this->setFirstAttribute($this->schema->commonName(), $name);
    }

    /**
     * Returns the model's name. An LDAP alias for the CN attribute.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms675449(v=vs.85).aspx
     *
     * @return string
     */
    public function getName()
    {
        return $this->getFirstAttribute($this->schema->name());
    }

    /**
     * Sets the model's name.
     *
     * @param string $name
     *
     * @return Model
     */
    public function setName($name)
    {
        return $this->setFirstAttribute($this->schema->name(), $name);
    }

    /**
     * Returns the model's display name.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->getFirstAttribute($this->schema->displayName());
    }

    /**
     * Sets the model's display name.
     *
     * @param string $displayName
     *
     * @return $this
     */
    public function setDisplayName($displayName)
    {
        return $this->setFirstAttribute($this->schema->displayName(), $displayName);
    }

    /**
     * Returns the model's samaccountname.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679635(v=vs.85).aspx
     *
     * @return string
     */
    public function getAccountName()
    {
        return $this->getFirstAttribute($this->schema->accountName());
    }

    /**
     * Sets the model's samaccountname.
     *
     * @param string $accountName
     *
     * @return Model
     */
    public function setAccountName($accountName)
    {
        return $this->setFirstAttribute($this->schema->accountName(), $accountName);
    }

    /**
     * Returns the model's userPrincipalName.
     *
     * @link https://docs.microsoft.com/en-us/windows/win32/adschema/a-userprincipalname
     *
     * @return string
     */
    public function getUserPrincipalName()
    {
        return $this->getFirstAttribute($this->schema->userPrincipalName());
    }

    /**
     * Sets the model's userPrincipalName.
     *
     * @param string $upn
     *
     * @return Model
     */
    public function setUserPrincipalName($upn)
    {
        return $this->setFirstAttribute($this->schema->userPrincipalName(), $upn);
    }

    /**
     * Returns the model's samaccounttype.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679637(v=vs.85).aspx
     *
     * @return string
     */
    public function getAccountType()
    {
        return $this->getFirstAttribute($this->schema->accountType());
    }

    /**
     * Returns the model's `whenCreated` time.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms680924(v=vs.85).aspx
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getFirstAttribute($this->schema->createdAt());
    }

    /**
     * Returns the created at time in a mysql formatted date.
     *
     * @return string
     */
    public function getCreatedAtDate()
    {
        return (new DateTime())->setTimestamp($this->getCreatedAtTimestamp())->format($this->dateFormat);
    }

    /**
     * Returns the created at time in a unix timestamp format.
     *
     * @return float
     */
    public function getCreatedAtTimestamp()
    {
        return DateTime::createFromFormat($this->timestampFormat, $this->getCreatedAt())->getTimestamp();
    }

    /**
     * Returns the model's `whenChanged` time.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms680921(v=vs.85).aspx
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getFirstAttribute($this->schema->updatedAt());
    }

    /**
     * Returns the updated at time in a mysql formatted date.
     *
     * @return string
     */
    public function getUpdatedAtDate()
    {
        return (new DateTime())->setTimestamp($this->getUpdatedAtTimestamp())->format($this->dateFormat);
    }

    /**
     * Returns the updated at time in a unix timestamp format.
     *
     * @return float
     */
    public function getUpdatedAtTimestamp()
    {
        return DateTime::createFromFormat($this->timestampFormat, $this->getUpdatedAt())->getTimestamp();
    }

    /**
     * Returns the Container of the current Model.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679012(v=vs.85).aspx
     *
     * @return Container|Entry|bool
     */
    public function getObjectClass()
    {
        return $this->query->findByDn($this->getObjectCategoryDn());
    }

    /**
     * Returns the CN of the model's object category.
     *
     * @return null|string
     */
    public function getObjectCategory()
    {
        $category = $this->getObjectCategoryArray();

        if (is_array($category) && array_key_exists(0, $category)) {
            return $category[0];
        }
    }

    /**
     * Returns the model's object category DN in an exploded array.
     *
     * @return array|false
     */
    public function getObjectCategoryArray()
    {
        return Utilities::explodeDn($this->getObjectCategoryDn());
    }

    /**
     * Returns the model's object category DN string.
     *
     * @return null|string
     */
    public function getObjectCategoryDn()
    {
        return $this->getFirstAttribute($this->schema->objectCategory());
    }

    /**
     * Returns the model's primary group ID.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679375(v=vs.85).aspx
     *
     * @return string
     */
    public function getPrimaryGroupId()
    {
        return $this->getFirstAttribute($this->schema->primaryGroupId());
    }

    /**
     * Returns the model's instance type.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms676204(v=vs.85).aspx
     *
     * @return int
     */
    public function getInstanceType()
    {
        return $this->getFirstAttribute($this->schema->instanceType());
    }

    /**
     * Returns the distinguished name of the user who is assigned to manage this object.
     *
     * @return string|null
     */
    public function getManagedBy()
    {
        return $this->getFirstAttribute($this->schema->managedBy());
    }

    /**
     * Returns the user model of the user who is assigned to manage this object.
     *
     * Returns false otherwise.
     *
     * @return User|bool
     */
    public function getManagedByUser()
    {
        if ($dn = $this->getManagedBy()) {
            return $this->query->newInstance()->findByDn($dn);
        }

        return false;
    }

    /**
     * Sets the user who is assigned to managed this object.
     *
     * @param Model|string $dn
     *
     * @return $this
     */
    public function setManagedBy($dn)
    {
        if ($dn instanceof self) {
            $dn = $dn->getDn();
        }

        return $this->setFirstAttribute($this->schema->managedBy(), $dn);
    }

    /**
     * Returns the model's max password age.
     *
     * @return string
     */
    public function getMaxPasswordAge()
    {
        return $this->getFirstAttribute($this->schema->maxPasswordAge());
    }

    /**
     * Returns the model's max password age in days.
     *
     * @return int
     */
    public function getMaxPasswordAgeDays()
    {
        $age = $this->getMaxPasswordAge();

        return (int) (abs($age) / 10000000 / 60 / 60 / 24);
    }

    /**
     * Determine if the current model is located inside the given OU.
     *
     * If a model instance is given, the strict parameter is ignored.
     *
     * @param Model|string $ou     The organizational unit to check.
     * @param bool         $strict Whether the check is case-sensitive.
     *
     * @return bool
     */
    public function inOu($ou, $strict = false)
    {
        if ($ou instanceof self) {
            // If we've been given an OU model, we can
            // just check if the OU's DN is inside
            // the current models DN.
            return (bool) strpos($this->getDn(), $ou->getDn());
        }

        $suffix = $strict ? '' : 'i';

        return (bool) preg_grep("/{$ou}/{$suffix}", $this->getDnBuilder()->getComponents('ou'));
    }

    /**
     * Returns true / false if the current model is writable
     * by checking its instance type integer.
     *
     * @return bool
     */
    public function isWritable()
    {
        return (int) $this->getInstanceType() === 4;
    }

    /**
     * Saves the changes to LDAP and returns the results.
     *
     * @param array $attributes The attributes to update or create for the current entry.
     *
     * @return bool
     */
    public function save(array $attributes = [])
    {
        $this->fireModelEvent(new Events\Saving($this));

        $saved = $this->exists ? $this->update($attributes) : $this->create($attributes);

        if ($saved) {
            $this->fireModelEvent(new Events\Saved($this));
        }

        return $saved;
    }

    /**
     * Updates the model.
     *
     * @param array $attributes The attributes to update for the current entry.
     *
     * @return bool
     */
    public function update(array $attributes = [])
    {
        $this->fill($attributes);

        $modifications = $this->getModifications();

        if (count($modifications) > 0) {
            $this->fireModelEvent(new Events\Updating($this));

            // Push the update.
            if ($this->query->getConnection()->modifyBatch($this->getDn(), $modifications)) {
                // Re-sync attributes.
                $this->syncRaw();

                $this->fireModelEvent(new Events\Updated($this));

                // Re-set the models modifications.
                $this->modifications = [];

                return true;
            }

            // Modification failed, return false.
            return false;
        }

        // We need to return true here because modify batch will
        // return false if no modifications are made
        // but this may not always be the case.
        return true;
    }

    /**
     * Creates the entry in LDAP.
     *
     * @param array $attributes The attributes for the new entry.
     *
     * @throws UnexpectedValueException
     *
     * @return bool
     */
    public function create(array $attributes = [])
    {
        $this->fill($attributes);

        if (empty($this->getDn())) {
            // If the model doesn't currently have a distinguished
            // name set, we'll create one automatically using
            // the current query builders base DN.
            $dn = $this->getCreatableDn();

            // If the dn we receive is the same as our queries base DN, we need
            // to throw an exception. The LDAP object must have a valid RDN.
            if ($dn->get() == $this->query->getDn()) {
                throw new UnexpectedValueException("An LDAP object must have a valid RDN to be created. '$dn' given.");
            }

            $this->setDn($dn);
        }

        $this->fireModelEvent(new Events\Creating($this));

        // Create the entry.
        $created = $this->query->getConnection()->add($this->getDn(), $this->getCreatableAttributes());

        if ($created) {
            // If the entry was created we'll re-sync
            // the models attributes from the server.
            $this->syncRaw();

            $this->fireModelEvent(new Events\Created($this));

            return true;
        }

        return false;
    }

    /**
     * Creates an attribute on the current model.
     *
     * @param string $attribute The attribute to create
     * @param mixed  $value     The value of the new attribute
     * @param bool   $sync      Whether to re-sync all attributes
     *
     * @return bool
     */
    public function createAttribute($attribute, $value, $sync = true)
    {
        if (
            $this->exists &&
            $this->query->getConnection()->modAdd($this->getDn(), [$attribute => $value])
        ) {
            if ($sync) {
                $this->syncRaw();
            }

            return true;
        }

        return false;
    }

    /**
     * Updates the specified attribute with the specified value.
     *
     * @param string $attribute The attribute to modify
     * @param mixed  $value     The new value for the attribute
     * @param bool   $sync      Whether to re-sync all attributes
     *
     * @return bool
     */
    public function updateAttribute($attribute, $value, $sync = true)
    {
        if (
            $this->exists &&
            $this->query->getConnection()->modReplace($this->getDn(), [$attribute => $value])
        ) {
            if ($sync) {
                $this->syncRaw();
            }

            return true;
        }

        return false;
    }

    /**
     * Deletes an attribute on the current entry.
     *
     * @param string|array $attributes The attribute(s) to delete
     * @param bool         $sync       Whether to re-sync all attributes
     *
     * Delete specific values in attributes:
     *
     *     ["memberuid" => "username"]
     *
     * Delete an entire attribute:
     *
     *     ["memberuid" => []]
     *
     * @return bool
     */
    public function deleteAttribute($attributes, $sync = true)
    {
        // If we've been given a string, we'll assume we're removing a
        // single attribute. Otherwise, we'll assume it's
        // an array of attributes to remove.
        $attributes = is_string($attributes) ? [$attributes => []] : $attributes;

        if (
            $this->exists &&
            $this->query->getConnection()->modDelete($this->getDn(), $attributes)
        ) {
            if ($sync) {
                $this->syncRaw();
            }

            return true;
        }

        return false;
    }

    /**
     * Deletes the current entry.
     *
     * Throws a ModelNotFoundException if the current model does
     * not exist or does not contain a distinguished name.
     *
     * @param bool $recursive Whether to recursively delete leaf nodes (models that are children).
     *
     * @throws ModelDoesNotExistException
     *
     * @return bool
     */
    public function delete($recursive = false)
    {
        $dn = $this->getDn();

        if ($this->exists === false || empty($dn)) {
            // Make sure the record exists before we can delete it.
            // Otherwise, we'll throw an exception.
            throw (new ModelDoesNotExistException())->setModel(get_class($this));
        }

        $this->fireModelEvent(new Events\Deleting($this));

        if ($recursive) {
            // If recursive is requested, we'll retrieve all direct leaf nodes
            // by executing a 'listing' and delete each resulting model.
            $this->newQuery()->listing()->in($this->getDn())->get()->each(function (self $model) use ($recursive) {
                $model->delete($recursive);
            });
        }

        if ($this->query->getConnection()->delete($dn)) {
            // If the deletion was successful, we'll mark the model
            // as non-existing and fire the deleted event.
            $this->exists = false;

            $this->fireModelEvent(new Events\Deleted($this));

            return true;
        }

        return false;
    }

    /**
     * Moves the current model into the given new parent.
     *
     * For example: $user->move($ou);
     *
     * @param Model|string $newParentDn  The new parent of the current model.
     * @param bool         $deleteOldRdn Whether to delete the old models relative distinguished name once renamed / moved.
     *
     * @return bool
     */
    public function move($newParentDn, $deleteOldRdn = true)
    {
        // First we'll explode the current models distinguished name and keep their attributes prefixes.
        $parts = Utilities::explodeDn($this->getDn(), $removeAttrPrefixes = false);

        // If the current model has an empty RDN, we can't move it.
        if ((int) Arr::first($parts) === 0) {
            throw new UnexpectedValueException('Current model does not contain an RDN to move.');
        }

        // Looks like we have a DN. We'll retrieve the leftmost RDN (the identifier).
        $rdn = Arr::get($parts, 0);

        return $this->rename($rdn, $newParentDn, $deleteOldRdn);
    }

    /**
     * Renames the current model to a new RDN and new parent.
     *
     * @param string            $rdn          The models new relative distinguished name. Example: "cn=JohnDoe"
     * @param Model|string|null $newParentDn  The models new parent distinguished name (if moving). Leave this null if you are only renaming. Example: "ou=MovedUsers,dc=acme,dc=org"
     * @param bool|true         $deleteOldRdn Whether to delete the old models relative distinguished name once renamed / moved.
     *
     * @return bool
     */
    public function rename($rdn, $newParentDn = null, $deleteOldRdn = true)
    {
        if ($newParentDn instanceof self) {
            $newParentDn = $newParentDn->getDn();
        }

        $moved = $this->query->getConnection()->rename($this->getDn(), $rdn, $newParentDn, $deleteOldRdn);

        if ($moved) {
            // If the model was successfully moved, we'll set its
            // new DN so we can sync it's attributes properly.
            $this->setDn("{$rdn},{$newParentDn}");

            $this->syncRaw();

            return true;
        }

        return false;
    }

    /**
     * Constructs a new distinguished name that is creatable in the directory.
     *
     * @return DistinguishedName|string
     */
    protected function getCreatableDn()
    {
        return $this->getDnBuilder()->addCn($this->getCommonName());
    }

    /**
     * Returns the models creatable attributes.
     *
     * @return mixed
     */
    protected function getCreatableAttributes()
    {
        return Arr::except($this->getAttributes(), [$this->schema->distinguishedName()]);
    }

    /**
     * Determines if the given modification is valid.
     *
     * @param mixed $mod
     *
     * @return bool
     */
    protected function isValidModification($mod)
    {
        return is_array($mod) &&
            array_key_exists(BatchModification::KEY_MODTYPE, $mod) &&
            array_key_exists(BatchModification::KEY_ATTRIB, $mod);
    }

    /**
     * Builds the models modifications from its dirty attributes.
     *
     * @return array
     */
    protected function buildModificationsFromDirty()
    {
        foreach ($this->getDirty() as $attribute => $values) {
            // Make sure values is always an array.
            $values = (is_array($values) ? $values : [$values]);

            // Create a new modification.
            $modification = $this->newBatchModification($attribute, null, $values);

            if (array_key_exists($attribute, $this->original)) {
                // If the attribute we're modifying has an original value, we'll give the
                // BatchModification object its values to automatically determine
                // which type of LDAP operation we need to perform.
                $modification->setOriginal($this->original[$attribute]);
            }

            // Build the modification from its
            // possible original values.
            $modification->build();

            if ($modification->isValid()) {
                // Finally, we'll add the modification to the model.
                $this->addModification($modification);
            }
        }

        return $this->modifications;
    }

    /**
     * Validates that the current LDAP connection is secure.
     *
     * @throws ConnectionException
     *
     * @return void
     */
    protected function validateSecureConnection()
    {
        if (!$this->query->getConnection()->canChangePasswords()) {
            throw new ConnectionException(
                'You must be connected to your LDAP server with TLS or SSL to perform this operation.'
            );
        }
    }

    /**
     * Converts the inserted string boolean to a PHP boolean.
     *
     * @param string $bool
     *
     * @return null|bool
     */
    protected function convertStringToBool($bool)
    {
        $bool = strtoupper($bool);

        if ($bool === strtoupper($this->schema->false())) {
            return false;
        } elseif ($bool === strtoupper($this->schema->true())) {
            return true;
        } else {
            return;
        }
    }
}
