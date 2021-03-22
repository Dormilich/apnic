# Change Log

## 1.3.0

- Updated object definitions according to what was found in the live database (version 1.88.15-45).
- Added `abuse-c` attribute in the `aut-num`, `inetnum`, and `inet6num` objects.

## 1.2.0

- BC Break: Dropped support for PHP 5
- Fixed: Renamed base object to avoid conflict with the reserved keyword in PHP 7.2+.

## 1.1.1

- Updated object definitions according to what was found in the live database (version 1.88.15-43) 
after a report that the `last-updated` attribute had finally made it into the live database.
- Added/updated attributes in the `mntner`, `route`, and `route6` objects.

## 1.1.0

- Updated object definitions according to what was defined in the test database.
- Removed the `changed` attribute from all objects.
- Added the `last-changed` attribute to all objects.
- Added/updated attributes in the `aut-num`, `domain`, and `organisation` objects.
