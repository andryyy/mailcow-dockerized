<?php

namespace Adldap\Models\Concerns;

trait HasUserProperties
{
    /**
     * Returns the users country.
     *
     * @return string|null
     */
    public function getCountry()
    {
        return $this->getFirstAttribute($this->schema->country());
    }

    /**
     * Sets the users country.
     *
     * @param string $country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        return $this->setFirstAttribute($this->schema->country(), $country);
    }

    /**
     * Returns the users department.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms675490(v=vs.85).aspx
     *
     * @return string|null
     */
    public function getDepartment()
    {
        return $this->getFirstAttribute($this->schema->department());
    }

    /**
     * Sets the users department.
     *
     * @param string $department
     *
     * @return $this
     */
    public function setDepartment($department)
    {
        return $this->setFirstAttribute($this->schema->department(), $department);
    }

    /**
     * Returns the users email address.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms676855(v=vs.85).aspx
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getFirstAttribute($this->schema->email());
    }

    /**
     * Sets the users email.
     *
     * Keep in mind this will remove all other
     * email addresses the user currently has.
     *
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setFirstAttribute($this->schema->email(), $email);
    }

    /**
     * Returns the users facsimile number.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms675675(v=vs.85).aspx
     *
     * @return string|null
     */
    public function getFacsimileNumber()
    {
        return $this->getFirstAttribute($this->schema->facsimile());
    }

    /**
     * Sets the users facsimile number.
     *
     * @param string $number
     *
     * @return $this
     */
    public function setFacsimileNumber($number)
    {
        return $this->setFirstAttribute($this->schema->facsimile(), $number);
    }

    /**
     * Returns the users first name.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms675719(v=vs.85).aspx
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getFirstAttribute($this->schema->firstName());
    }

    /**
     * Sets the users first name.
     *
     * @param string $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName)
    {
        return $this->setFirstAttribute($this->schema->firstName(), $firstName);
    }

    /**
     * Returns the users initials.
     *
     * @return string|null
     */
    public function getInitials()
    {
        return $this->getFirstAttribute($this->schema->initials());
    }

    /**
     * Sets the users initials.
     *
     * @param string $initials
     *
     * @return $this
     */
    public function setInitials($initials)
    {
        return $this->setFirstAttribute($this->schema->initials(), $initials);
    }

    /**
     * Returns the users IP Phone.
     *
     * @return string|null
     */
    public function getIpPhone()
    {
        return $this->getFirstAttribute($this->schema->ipPhone());
    }

    /**
     * Sets the users IP phone.
     *
     * @param string $ip
     *
     * @return $this
     */
    public function setIpPhone($ip)
    {
        return $this->setFirstAttribute($this->schema->ipPhone(), $ip);
    }

    /**
     * Returns the users last name.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679872(v=vs.85).aspx
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->getFirstAttribute($this->schema->lastName());
    }

    /**
     * Sets the users last name.
     *
     * @param string $lastName
     *
     * @return $this
     */
    public function setLastName($lastName)
    {
        return $this->setFirstAttribute($this->schema->lastName(), $lastName);
    }

    /**
     * Returns the users postal code.
     *
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->getFirstAttribute($this->schema->postalCode());
    }

    /**
     * Sets the users postal code.
     *
     * @param string $postalCode
     *
     * @return $this
     */
    public function setPostalCode($postalCode)
    {
        return $this->setFirstAttribute($this->schema->postalCode(), $postalCode);
    }

    /**
     * Get the users post office box.
     *
     * @return string|null
     */
    public function getPostOfficeBox()
    {
        return $this->getFirstAttribute($this->schema->postOfficeBox());
    }

    /**
     * Sets the users post office box.
     *
     * @param string|int $box
     *
     * @return $this
     */
    public function setPostOfficeBox($box)
    {
        return $this->setFirstAttribute($this->schema->postOfficeBox(), $box);
    }

    /**
     * Sets the users proxy addresses.
     *
     * This will remove all proxy addresses on the user and insert the specified addresses.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679424(v=vs.85).aspx
     *
     * @param array $addresses
     *
     * @return $this
     */
    public function setProxyAddresses(array $addresses = [])
    {
        return $this->setAttribute($this->schema->proxyAddresses(), $addresses);
    }

    /**
     * Add's a single proxy address to the user.
     *
     * @param string $address
     *
     * @return $this
     */
    public function addProxyAddress($address)
    {
        $addresses = $this->getProxyAddresses();

        $addresses[] = $address;

        return $this->setAttribute($this->schema->proxyAddresses(), $addresses);
    }

    /**
     * Returns the users proxy addresses.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679424(v=vs.85).aspx
     *
     * @return array
     */
    public function getProxyAddresses()
    {
        return $this->getAttribute($this->schema->proxyAddresses()) ?? [];
    }

    /**
     * Returns the users street address.
     *
     * @return string|null
     */
    public function getStreetAddress()
    {
        return $this->getFirstAttribute($this->schema->streetAddress());
    }

    /**
     * Sets the users street address.
     *
     * @param string $address
     *
     * @return $this
     */
    public function setStreetAddress($address)
    {
        return $this->setFirstAttribute($this->schema->streetAddress(), $address);
    }

    /**
     * Returns the users title.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms680037(v=vs.85).aspx
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->getFirstAttribute($this->schema->title());
    }

    /**
     * Sets the users title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setFirstAttribute($this->schema->title(), $title);
    }

    /**
     * Returns the users telephone number.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms680027(v=vs.85).aspx
     *
     * @return string|null
     */
    public function getTelephoneNumber()
    {
        return $this->getFirstAttribute($this->schema->telephone());
    }

    /**
     * Sets the users telephone number.
     *
     * @param string $number
     *
     * @return $this
     */
    public function setTelephoneNumber($number)
    {
        return $this->setFirstAttribute($this->schema->telephone(), $number);
    }

    /**
     * Returns the users primary mobile phone number.
     *
     * @return string|null
     */
    public function getMobileNumber()
    {
        return $this->getFirstAttribute($this->schema->mobile());
    }

    /**
     * Sets the users primary mobile phone number.
     *
     * @param string $number
     *
     * @return $this
     */
    public function setMobileNumber($number)
    {
        return $this->setFirstAttribute($this->schema->mobile(), $number);
    }

    /**
     * Returns the users secondary (other) mobile phone number.
     *
     * @return string|null
     */
    public function getOtherMobileNumber()
    {
        return $this->getFirstAttribute($this->schema->otherMobile());
    }

    /**
     * Sets the users  secondary (other) mobile phone number.
     *
     * @param string $number
     *
     * @return $this
     */
    public function setOtherMobileNumber($number)
    {
        return $this->setFirstAttribute($this->schema->otherMobile(), $number);
    }

    /**
     * Returns the users other mailbox attribute.
     *
     * @link https://msdn.microsoft.com/en-us/library/ms679091(v=vs.85).aspx
     *
     * @return array
     */
    public function getOtherMailbox()
    {
        return $this->getAttribute($this->schema->otherMailbox());
    }

    /**
     * Sets the users other mailboxes.
     *
     * @param array $otherMailbox
     *
     * @return $this
     */
    public function setOtherMailbox($otherMailbox = [])
    {
        return $this->setAttribute($this->schema->otherMailbox(), $otherMailbox);
    }

    /**
     * Returns the distinguished name of the user who is the user's manager.
     *
     * @return string|null
     */
    public function getManager()
    {
        return $this->getFirstAttribute($this->schema->manager());
    }

    /**
     * Sets the distinguished name of the user who is the user's manager.
     *
     * @param string $managerDn
     *
     * @return $this
     */
    public function setManager($managerDn)
    {
        return $this->setFirstAttribute($this->schema->manager(), $managerDn);
    }

    /**
     * Returns the users mail nickname.
     *
     * @return string|null
     */
    public function getMailNickname()
    {
        return $this->getFirstAttribute($this->schema->emailNickname());
    }
}
