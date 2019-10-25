OAWM-Extension
======================

Settings for community editors for IPOA

* More restrictions to visibility of content elements
* Forwarding after login
* Adapted content elements
* Adaptations for tt_address tables

## Profiles
To be visible, community editors have to
* add a new backend user, fill in the required fields
* add a tt_address-author with the uid of the backend user
* add a tt_address listview plugin and a place for detail view 

## Required extensions
* tt_address
* blog
* frontend editing
