<?php

namespace Adldap\Schemas;

use Adldap\Models\User;
use Adldap\Models\Entry;
use Adldap\Models\Group;
use Adldap\Models\Contact;
use Adldap\Models\Printer;
use Adldap\Models\Computer;
use Adldap\Models\Container;
use Adldap\Models\Organization;
use Adldap\Models\OrganizationalUnit;
use Adldap\Models\ForeignSecurityPrincipal;

abstract class Schema implements SchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function accountExpires()
    {
        return 'accountexpires';
    }

    /**
     * {@inheritdoc}
     */
    public function accountName()
    {
        return 'samaccountname';
    }

    /**
     * {@inheritdoc}
     */
    public function accountType()
    {
        return 'samaccounttype';
    }

    /**
     * {@inheritdoc}
     */
    public function adminDisplayName()
    {
        return 'admindisplayname';
    }

    /**
     * {@inheritdoc}
     */
    public function anr()
    {
        return 'anr';
    }

    /**
     * {@inheritdoc}
     */
    public function badPasswordCount()
    {
        return 'badpwdcount';
    }

    /**
     * {@inheritdoc}
     */
    public function badPasswordTime()
    {
        return 'badpasswordtime';
    }

    /**
     * {@inheritdoc}
     */
    public function commonName()
    {
        return 'cn';
    }

    /**
     * {@inheritdoc}
     */
    public function company()
    {
        return 'company';
    }

    /**
     * {@inheritdoc}
     */
    public function computer()
    {
        return 'computer';
    }

    /**
     * {@inheritdoc}
     */
    public function computerModel()
    {
        return Computer::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configurationNamingContext()
    {
        return 'configurationnamingcontext';
    }

    /**
     * {@inheritdoc}
     */
    public function contact()
    {
        return 'contact';
    }

    /**
     * {@inheritdoc}
     */
    public function contactModel()
    {
        return Contact::class;
    }

    /**
     * {@inheritdoc}
     */
    public function containerModel()
    {
        return Container::class;
    }

    /**
     * {@inheritdoc}
     */
    public function country()
    {
        return 'c';
    }

    /**
     * {@inheritdoc}
     */
    public function createdAt()
    {
        return 'whencreated';
    }

    /**
     * {@inheritdoc}
     */
    public function currentTime()
    {
        return 'currenttime';
    }

    /**
     * {@inheritdoc}
     */
    public function defaultNamingContext()
    {
        return 'defaultnamingcontext';
    }

    /**
     * {@inheritdoc}
     */
    public function department()
    {
        return 'department';
    }

    /**
     * {@inheritdoc}
     */
    public function departmentNumber()
    {
        return 'departmentnumber';
    }

    /**
     * {@inheritdoc}
     */
    public function description()
    {
        return 'description';
    }

    /**
     * {@inheritdoc}
     */
    public function displayName()
    {
        return 'displayname';
    }

    /**
     * {@inheritdoc}
     */
    public function dnsHostName()
    {
        return 'dnshostname';
    }

    /**
     * {@inheritdoc}
     */
    public function domainComponent()
    {
        return 'dc';
    }

    /**
     * {@inheritdoc}
     */
    public function driverName()
    {
        return 'drivername';
    }

    /**
     * {@inheritdoc}
     */
    public function driverVersion()
    {
        return 'driverversion';
    }

    /**
     * {@inheritdoc}
     */
    public function email()
    {
        return 'mail';
    }

    /**
     * {@inheritdoc}
     */
    public function emailNickname()
    {
        return 'mailnickname';
    }

    /**
     * {@inheritdoc}
     */
    public function employeeId()
    {
        return 'employeeid';
    }

    /**
     * {@inheritdoc}
     */
    public function employeeNumber()
    {
        return 'employeenumber';
    }

    /**
     * {@inheritdoc}
     */
    public function employeeType()
    {
        return 'employeetype';
    }

    /**
     * {@inheritdoc}
     */
    public function entryModel()
    {
        return Entry::class;
    }

    /**
     * {@inheritdoc}
     */
    public function false()
    {
        return 'FALSE';
    }

    /**
     * {@inheritdoc}
     */
    public function firstName()
    {
        return 'givenname';
    }

    /**
     * {@inheritdoc}
     */
    public function groupModel()
    {
        return Group::class;
    }

    /**
     * {@inheritdoc}
     */
    public function groupType()
    {
        return 'grouptype';
    }

    /**
     * {@inheritdoc}
     */
    public function homeAddress()
    {
        return 'homepostaladdress';
    }

    /**
     * {@inheritdoc}
     */
    public function homeMdb()
    {
        return 'homemdb';
    }

    /**
     * {@inheritdoc}
     */
    public function homeDrive()
    {
        return 'homedrive';
    }

    /**
     * {@inheritdoc}
     */
    public function homeDirectory()
    {
        return 'homedirectory';
    }

    /**
     * {@inheritdoc}
     */
    public function homePhone()
    {
        return 'homephone';
    }

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        return 'info';
    }

    /**
     * {@inheritdoc}
     */
    public function initials()
    {
        return 'initials';
    }

    /**
     * {@inheritdoc}
     */
    public function instanceType()
    {
        return 'instancetype';
    }

    /**
     * {@inheritdoc}
     */
    public function ipPhone()
    {
        return 'ipphone';
    }

    /**
     * {@inheritdoc}
     */
    public function isCriticalSystemObject()
    {
        return 'iscriticalsystemobject';
    }

    /**
     * {@inheritdoc}
     */
    public function jpegPhoto()
    {
        return 'jpegphoto';
    }

    /**
     * {@inheritdoc}
     */
    public function lastLogOff()
    {
        return 'lastlogoff';
    }

    /**
     * {@inheritdoc}
     */
    public function lastLogOn()
    {
        return 'lastlogon';
    }

    /**
     * {@inheritdoc}
     */
    public function lastLogOnTimestamp()
    {
        return 'lastlogontimestamp';
    }

    /**
     * {@inheritdoc}
     */
    public function lastName()
    {
        return 'sn';
    }

    /**
     * {@inheritdoc}
     */
    public function legacyExchangeDn()
    {
        return 'legacyexchangedn';
    }

    /**
     * {@inheritdoc}
     */
    public function locale()
    {
        return 'l';
    }

    /**
     * {@inheritdoc}
     */
    public function location()
    {
        return 'location';
    }

    /**
     * {@inheritdoc}
     */
    public function manager()
    {
        return 'manager';
    }

    /**
     * {@inheritdoc}
     */
    public function managedBy()
    {
        return 'managedby';
    }

    /**
     * {@inheritdoc}
     */
    public function maxPasswordAge()
    {
        return 'maxpwdage';
    }

    /**
     * {@inheritdoc}
     */
    public function member()
    {
        return 'member';
    }

    /**
     * {@inheritdoc}
     */
    public function memberIdentifier()
    {
        return 'distinguishedname';
    }

    /**
     * {@inheritdoc}
     */
    public function memberOf()
    {
        return 'memberof';
    }

    /**
     * {@inheritdoc}
     */
    public function memberOfRecursive()
    {
        return 'memberof:1.2.840.113556.1.4.1941:';
    }

    /**
     * {@inheritdoc}
     */
    public function memberRange($from, $to)
    {
        return $this->member().";range={$from}-{$to}";
    }

    /**
     * {@inheritdoc}
     */
    public function messageTrackingEnabled()
    {
        return 'messagetrackingenabled';
    }

    /**
     * {@inheritdoc}
     */
    public function msExchangeServer()
    {
        return 'ms-exch-exchange-server';
    }

    /**
     * {@inheritdoc}
     */
    public function name()
    {
        return 'name';
    }

    /**
     * {@inheritdoc}
     */
    public function neverExpiresDate()
    {
        return '9223372036854775807';
    }

    /**
     * {@inheritdoc}
     */
    public function objectCategoryComputer()
    {
        return 'computer';
    }

    /**
     * {@inheritdoc}
     */
    public function objectCategoryContainer()
    {
        return 'container';
    }

    /**
     * {@inheritdoc}
     */
    public function objectCategoryExchangePrivateMdb()
    {
        return 'msexchprivatemdb';
    }

    /**
     * {@inheritdoc}
     */
    public function objectCategoryExchangeServer()
    {
        return 'msExchExchangeServer';
    }

    /**
     * {@inheritdoc}
     */
    public function objectCategoryExchangeStorageGroup()
    {
        return 'msExchStorageGroup';
    }

    /**
     * {@inheritdoc}
     */
    public function objectCategoryGroup()
    {
        return 'group';
    }

    /**
     * {@inheritdoc}
     */
    public function objectCategoryOrganizationalUnit()
    {
        return 'organizational-unit';
    }

    /**
     * {@inheritdoc}
     */
    public function objectCategoryPerson()
    {
        return 'person';
    }

    /**
     * {@inheritdoc}
     */
    public function objectCategoryPrinter()
    {
        return 'print-queue';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClass()
    {
        return 'objectclass';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassComputer()
    {
        return 'computer';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassContact()
    {
        return 'contact';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassContainer()
    {
        return 'container';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassOrganization()
    {
        return 'organization';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassPrinter()
    {
        return 'printqueue';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassUser()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassForeignSecurityPrincipal()
    {
        return 'foreignsecurityprincipal';
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassModelMap()
    {
        return [
            $this->objectClassComputer()                    => $this->computerModel(),
            $this->objectClassContact()                     => $this->contactModel(),
            $this->objectClassPerson()                      => $this->userModel(),
            $this->objectClassGroup()                       => $this->groupModel(),
            $this->objectClassContainer()                   => $this->containerModel(),
            $this->objectClassPrinter()                     => $this->printerModel(),
            $this->objectClassOrganization()                => $this->organizationModel(),
            $this->objectClassOu()                          => $this->organizationalUnitModel(),
            $this->objectClassForeignSecurityPrincipal()    => $this->foreignSecurityPrincipalModel(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function objectSid()
    {
        return 'objectsid';
    }

    /**
     * {@inheritdoc}
     */
    public function objectSidRequiresConversion()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function operatingSystem()
    {
        return 'operatingsystem';
    }

    /**
     * {@inheritdoc}
     */
    public function operatingSystemServicePack()
    {
        return 'operatingsystemservicepack';
    }

    /**
     * {@inheritdoc}
     */
    public function operatingSystemVersion()
    {
        return 'operatingsystemversion';
    }

    /**
     * {@inheritdoc}
     */
    public function organization()
    {
        return 'organization';
    }

    /**
     * {@inheritdoc}
     */
    public function organizationName()
    {
        return 'o';
    }

    /**
     * {@inheritdoc}
     */
    public function organizationalPerson()
    {
        return 'organizationalperson';
    }

    /**
     * {@inheritdoc}
     */
    public function organizationalUnit()
    {
        return 'organizationalunit';
    }

    /**
     * {@inheritdoc}
     */
    public function organizationalUnitModel()
    {
        return OrganizationalUnit::class;
    }

    /**
     * {@inheritdoc}
     */
    public function organizationModel()
    {
        return Organization::class;
    }

    /**
     * {@inheritdoc}
     */
    public function organizationalUnitShort()
    {
        return 'ou';
    }

    /**
     * {@inheritdoc}
     */
    public function otherMailbox()
    {
        return 'othermailbox';
    }

    /**
     * {@inheritdoc}
     */
    public function passwordLastSet()
    {
        return 'pwdlastset';
    }

    /**
     * {@inheritdoc}
     */
    public function person()
    {
        return 'person';
    }

    /**
     * {@inheritdoc}
     */
    public function personalTitle()
    {
        return 'personaltitle';
    }

    /**
     * {@inheritdoc}
     */
    public function physicalDeliveryOfficeName()
    {
        return 'physicaldeliveryofficename';
    }

    /**
     * {@inheritdoc}
     */
    public function portName()
    {
        return 'portname';
    }

    /**
     * {@inheritdoc}
     */
    public function postalCode()
    {
        return 'postalcode';
    }

    /**
     * {@inheritdoc}
     */
    public function postOfficeBox()
    {
        return 'postofficebox';
    }

    /**
     * {@inheritdoc}
     */
    public function primaryGroupId()
    {
        return 'primarygroupid';
    }

    /**
     * {@inheritdoc}
     */
    public function printerBinNames()
    {
        return 'printbinnames';
    }

    /**
     * {@inheritdoc}
     */
    public function printerColorSupported()
    {
        return 'printcolor';
    }

    /**
     * {@inheritdoc}
     */
    public function printerDuplexSupported()
    {
        return 'printduplexsupported';
    }

    /**
     * {@inheritdoc}
     */
    public function printerEndTime()
    {
        return 'printendtime';
    }

    /**
     * {@inheritdoc}
     */
    public function printerMaxResolutionSupported()
    {
        return 'printmaxresolutionsupported';
    }

    /**
     * {@inheritdoc}
     */
    public function printerMediaSupported()
    {
        return 'printmediasupported';
    }

    /**
     * {@inheritdoc}
     */
    public function printerMemory()
    {
        return 'printmemory';
    }

    /**
     * {@inheritdoc}
     */
    public function printerModel()
    {
        return Printer::class;
    }

    /**
     * {@inheritdoc}
     */
    public function printerName()
    {
        return 'printername';
    }

    /**
     * {@inheritdoc}
     */
    public function printerOrientationSupported()
    {
        return 'printorientationssupported';
    }

    /**
     * {@inheritdoc}
     */
    public function printerPrintRate()
    {
        return 'printrate';
    }

    /**
     * {@inheritdoc}
     */
    public function printerPrintRateUnit()
    {
        return 'printrateunit';
    }

    /**
     * {@inheritdoc}
     */
    public function printerShareName()
    {
        return 'printsharename';
    }

    /**
     * {@inheritdoc}
     */
    public function printerStaplingSupported()
    {
        return 'printstaplingsupported';
    }

    /**
     * {@inheritdoc}
     */
    public function printerStartTime()
    {
        return 'printstarttime';
    }

    /**
     * {@inheritdoc}
     */
    public function priority()
    {
        return 'priority';
    }

    /**
     * {@inheritdoc}
     */
    public function profilePath()
    {
        return 'profilepath';
    }

    /**
     * {@inheritdoc}
     */
    public function proxyAddresses()
    {
        return 'proxyaddresses';
    }

    /**
     * {@inheritdoc}
     */
    public function roomNumber()
    {
        return 'roomnumber';
    }

    /**
     * {@inheritdoc}
     */
    public function rootDomainNamingContext()
    {
        return 'rootdomainnamingcontext';
    }

    /**
     * {@inheritdoc}
     */
    public function schemaNamingContext()
    {
        return 'schemanamingcontext';
    }

    /**
     * {@inheritdoc}
     */
    public function scriptPath()
    {
        return 'scriptpath';
    }

    /**
     * {@inheritdoc}
     */
    public function serialNumber()
    {
        return 'serialnumber';
    }

    /**
     * {@inheritdoc}
     */
    public function serverName()
    {
        return 'servername';
    }

    /**
     * {@inheritdoc}
     */
    public function showInAddressBook()
    {
        return 'showinaddressbook';
    }

    /**
     * {@inheritdoc}
     */
    public function street()
    {
        return 'street';
    }

    /**
     * {@inheritdoc}
     */
    public function streetAddress()
    {
        return 'streetaddress';
    }

    /**
     * {@inheritdoc}
     */
    public function systemFlags()
    {
        return 'systemflags';
    }

    /**
     * {@inheritdoc}
     */
    public function telephone()
    {
        return 'telephonenumber';
    }

    /**
     * {@inheritdoc}
     */
    public function mobile()
    {
        return 'mobile';
    }

    /**
     * {@inheritdoc}
     */
    public function otherMobile()
    {
        return 'othermobile';
    }

    /**
     * {@inheritdoc}
     */
    public function facsimile()
    {
        return 'facsimiletelephonenumber';
    }

    /**
     * {@inheritdoc}
     */
    public function thumbnail()
    {
        return 'thumbnailphoto';
    }

    /**
     * {@inheritdoc}
     */
    public function title()
    {
        return 'title';
    }

    /**
     * {@inheritdoc}
     */
    public function top()
    {
        return 'top';
    }

    /**
     * {@inheritdoc}
     */
    public function true()
    {
        return 'TRUE';
    }

    /**
     * {@inheritdoc}
     */
    public function unicodePassword()
    {
        return 'unicodepwd';
    }

    /**
     * {@inheritdoc}
     */
    public function updatedAt()
    {
        return 'whenchanged';
    }

    /**
     * {@inheritdoc}
     */
    public function url()
    {
        return 'url';
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function userAccountControl()
    {
        return 'useraccountcontrol';
    }

    /**
     * {@inheritdoc}
     */
    public function userId()
    {
        return 'uid';
    }

    /**
     * {@inheritdoc}
     */
    public function userModel()
    {
        return User::class;
    }

    /**
     * {@inheritdoc}
     */
    public function userObjectClasses(): array
    {
        return [
            $this->top(),
            $this->person(),
            $this->organizationalPerson(),
            $this->objectClassUser(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function userPrincipalName()
    {
        return 'userprincipalname';
    }

    /**
     * {@inheritdoc}
     */
    public function userWorkstations()
    {
        return 'userworkstations';
    }

    /**
     * {@inheritdoc}
     */
    public function versionNumber()
    {
        return 'versionnumber';
    }

    /**
     * {@inheritdoc}
     */
    public function foreignSecurityPrincipalModel()
    {
        return ForeignSecurityPrincipal::class;
    }
}
