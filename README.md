IPOA Template
======================

Template for IPOA homepage.

It consists of two different layouts for start page and content pages.

(To be able to switch between layouts, you have to configure a backend layout for the startpage in the backend)

# Requirements

## Dependency on other extensions
	* vhs (vhs)
	* Apache Solr (solr)
	* Twitter feed (cw_twitter)

## Required TypoScript in BE
*   Template of root page:
	*   Constants:
		*   Including the Template:
		```
				<INCLUDE_TYPOSCRIPT: source="FILE: typo3conf/ext/tmpl_ipoa/Configuration/TypoScript/Static/constants.txt">
		```
		*   Definitions for the design and the menu of the page as its logo, its header.
		Special pages are also defined like the ids of start, kontakt, impressum, news, ueberUns, error and search.
		Here are also the definitions of the backend layouts.
		```
				ids {
				    header_title = Open Access
					header_claim = Open Access to <br />Scientific Information
					header_image = /fileadmin/logos/oa.svg
					excludeFromMenu = 11,221,321,117
					start = 32
					kontakt = 363
					sitemap = 136
					impressum = 6
					news = 121
					search = 79
					error = 708
					backendLayout {
						startseite = 3
						content = 6
						oatage = 4
					}
				}
		```
	*   Setup:
	```
			<INCLUDE_TYPOSCRIPT: source="FILE: typo3conf/ext/tmpl_ipoa/Configuration/TypoScript/Static/setup.txt">
	```
*   Template for start page:
	*   Constants
	You can display up to three buttons on the start page. Therefore you have to define their pids as well as their text.
	If you empty one buttons uid, it will not be displayed.
	```
			ids {
				startbutton_1 = 210
				startbutton_1.title = Some information
				startbutton_2 = 116
				startbutton_2.title = More information
				startbutton_3 >
				startbutton_3.title = Even more information
			}
	```
			
## Build and development requirements
You need to have nvm and npm installed for package management, linting and building css and js files. The correct version of npm can be chosen with nvm
```
		$ nvm use
		$ npm install
		$ npm run devAll // for development
		$ npm run buildAll // for production
```
