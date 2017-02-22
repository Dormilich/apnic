# APNIC data objects

A PHP library to read, write, and validate APNIC RPSL objects.

## Reading Objects

The utility class `WhoisParser` can read the output retrieved from the `whois` command and turn it into RPSL objects.

```php
use Dormilich\APNIC\Utilities\WhoisParser;

$reader = new WhoisParser;

// of course you can use any CLI processor you like
$whois = `whois '192.186.2.0 - 192.186.2.8' -h whois.apnic.net`; # using GNU whois here

try {
  if ($net = $reader->parse($whois)) {
    // do something with the Inetnum object ...
  }
}
catch (Exception $e) {
  // ... or show any problems you got while parsing
  echo $e;
}
```
If you are certain which object to expect, you can feed it to the parser. This will always get you an object back.
```php
use Dormilich\APNIC\Utilities\WhoisParser;
use Dormilich\APNIC\RPSL\Inetnum;

$reader = new WhoisParser;

$whois = `whois '192.186.2.0 - 192.186.2.8' -h whois.apnic.net -rxB`;

try {
  // unlike the first example, $net will always be an RPSL object
  $net = $reader->parse($whois, new Inetnum(null));

  if (!$net->isValid()) {
    // something is left out
  }

  // do something with the Inetnum object ...
}
catch (Exception $e) {
  echo $e;
}
```
If you want to read all RPSL objects from the output, that’s also possible. The only downside is that you don’t know beforehand what’s all inside …
```php
use Dormilich\APNIC\Utilities\WhoisParser;

$reader = new WhoisParser;

$whois = `whois '192.186.2.0 - 192.186.2.8' -h whois.apnic.net -rxB`;

try {
  $data = $reader->parseAll($whois);
  
  $net = $data['192.186.2.0 - 192.186.2.8'];
  $admin1 = $data[ $net['admin-c'][0] ];
  
  // ...
}
catch (Exception $e) {
  echo $e;
}

```
## Working with Objects

The RPSL objects allow to conveniently edit RPSL data.
```php
use Dormilich\APNIC\RPSL\Person;

$person = new Person;

// set a value to an attribute
$person['person'] = 'John Doe';
$person->set('source', 'APNIC');

// add values to a multiple attribute
$person
  ->add('address', 'infinity drive 1')
  ->add('address', 'anytown')
;

// get a value from an attribute
echo $person['person']; // John Doe
echo $person->get('source'); // APNIC
$address = $person->get('address'); // ['infinity drive 1', 'anytown']

// delete values
unset($person['source']);
var_dump( $person->get('source') ); // NULL
```
But you’re not limited to primitive values, attributes that accept handles allow the appropriate object as input. And for the most common attributes, these even get validated.
```php
// you can even pass RPSL objects to appropriate attributes
$maint = ... // get that object from whois
$obj['mnt-by'] = $maint;

// common attributes that contain references validate any passed RPSL object
// e.g. this throws an exception since tech contacts can only be Person or Role handles
$obj->add('tech-c', $maint);
```
Templates … for that the RPSL objects and their attributes are iterable.

## Writing Objects

'Writing' may be a bit of an exaggeration. Essentially, printing an object creates its textual representation that you can use in an email update.
```php
use Dormilich\APNIC\Utilities\WhoisParser;

$reader = new WhoisParser;

$whois = `whois JD12-AP -h whois.apnic.net -rB`;

try {
  if ($john = $reader->parse($whois)) {
    $john
      ->add('phone', '+1 234 567 8901')
      ->add('changed', 'john.doe@example.com')
    ;
    $body = $john . 'password: ' . $my_apnic_password;
    // please use a proper email client!
    mail('auto-dbm@apnic.net', $john->getHandle(), $body, ...);
  }
}
catch (Exception $e) {
  echo $e;
}
```
