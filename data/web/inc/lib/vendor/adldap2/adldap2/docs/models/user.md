# The User Model

> **Note**: This model contains the trait `HasMemberOf`. For more information, visit the documentation:
> [HasMemberOfTrait](/models/traits/has-member-of.md)

## Creating

> **Note**: If you need to create users with passwords, SSL or TLS **must** be enabled on your configured connection.
> 
> The password you enter for the user **must** also obey your LDAP servers password requirements,
> otherwise you will receive a "Server is unwilling to perform" LDAP exception upon saving.

```php
// Construct a new User model instance.
$user = $provider->make()->user();

// Create the users distinguished name.
// We're adding an OU onto the users base DN to have it be saved in the specified OU.
$dn = $user->getDnBuilder()->addOu('Users'); // Built DN will be: "CN=John Doe,OU=Users,DC=acme,DC=org";

// Set the users DN, account name.
$user->setDn($dn);
$user->setAccountName('jdoe');
$user->setCommonName('John Doe');

// Set the users password.
// NOTE: This password must obey your AD servers password requirements
// (including password history, length, special characters etc.)
// otherwise saving will fail and you will receive an
// "LDAP Server is unwilling to perform" message.
$user->setPassword('correct-horse-battery-staple');

// Get a new account control object for the user.
$ac = $user->getUserAccountControlObject();

// Mark the account as enabled (normal).
$ac->accountIsNormal();

// Set the account control on the user and save it.
$user->setUserAccountControl($ac);

// Save the user.
$user->save();

// All done! An enabled user will be created and is ready for use.
```

## Methods

There's a ton of available methods for the User model. Below is a list for a quick reference.

> **Note**: Don't see a method for an LDAP attribute? Create an issue and let us know!

```php
// Get the users display name.
$user->getDisplayName();

// Get the users first email address.
$user->getEmail();

// Get the users title.
$user->getTitle();

// Get the users department.
$user->getDepartment();

// Get the users first name.
$user->getFirstName();

// Get the users last name.
$user->getLastName();

// Get the users info.
$user->getInfo();

// Get the users initials.
$user->getInitials();

// Get the users country.
$user->getCountry();

// Get the users street address.
$user->getStreetAddress();

// Get the users postal code.
$user->getPostalCode();

// Get the users physical delivery office name.
$user->getPhysicalDeliveryOfficeName();

// Get the users phone number.
$user->getTelephoneNumber();

// Get the users locale.
$user->getLocale();

// Get the users company.
$user->getCompany();

// Get the users other email addresses.
$user->getOtherMailbox();

// Get the users home mailbox database location (stored as a distinguished name). 
$user->getHomeMdb();

// Get the users email nickname.
$user->getMailNickname();

// Get the users principal name.
$user->getUserPrincipalName();

// Get the users proxy email addresses.
$user->getProxyAddresses();

// Get the users failed login attempts.
$user->getBadPasswordCount();

// Get the users last failed login attempt timestamp.
$user->getBadPasswordTime();

// Get the users last password change timestamp.
$user->getPasswordLastSet();

// Get the users last password change timestamp in unix time.
$user->getPasswordLastSetTimestamp();

// Get the users last password change timestamp in MySQL date format.
$user->getPasswordLastSetDate();

// Get the users lockout time.
$user->getLockoutTime();

// Get the users user account control integer.
$user->getUserAccountControl();

// Get the users roaming profile path.
$user->getProfilePath();

// Get the users legacy exchange distinguished name.
$user->getLegacyExchangeDn();

// Get the users account expiry timestamp.
$user->getAccountExpiry();

// Get the boolean that determines whether to show this user in the global address book.
$user->getShowInAddressBook();

// Get the users thumbnail photo.
$user->getThumbnail();

// Get the users thumbnail photo (base64 encoded for HTML <img src=""> tags).
$user->getThumbnailEncoded();

// Get the users jpeg photo.
$user->getJpegPhoto();

// Get the users jpeg photo (base64 encoded for HTML <img src=""> tags).
$user->getJpegPhotoEncoded();

// Get the users manager.
$user->getManager();

// Get the users employee ID.
$user->getEmployeeId();

// Get the users employee number.
$user->getEmployeeNumber();

// Get the users employee type
$user->getEmployeeType();

// Get the users room number.
$user->getRoomNumber();

// Get the users department number.
$user->getDepartmentNumber();

// Get the users personal title.
$user->getPersonalTitle();
```
