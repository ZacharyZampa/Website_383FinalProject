# Website_383FinalProject

## One Minute Demo of Website

https://www.youtube.com/watch?v=48plCjrQUSQ

## Short Report About Project

https://docs.google.com/document/d/1QPoAmgdUB2yJ1qsjbib5Tlk4FNToVUORHsA_lzJmnlk/edit?usp=sharing

## Base Overview

### Backend

Utilizes Miami University's CAS Authentication to ensure a user must be logged in to visit the website. It also uses this service to determine if user is authorized to access the configuration pages for Quick Links or Video Links. The website uses REST APIs to access Weather as well as the links. As a further method of security, the REST API requires a token that corresponds to one an authorized user has to allow for modification of the database. The technology was designed in a way so that the database can be hosted on another server than the site itself, allowing for more flexibility.

### FrontEnd

If a user is not authorized for use of the configuration pages for Quick Links and Video Links, the index page hides the choices for those through PHP. This helps obfusicate and make it more difficult for malicious users. The set weather links and the image of me utilize modals for what I believe results in a prettier layout and easier use for mobile users. Another quality of life feature is the scrollspy navbar. It allows for a user to jump to a particular location on the page rather than scrolling. To make the embeded videos more compact and take up less room, they are encoded within an accordion. This way, when the page loads, all are closed and only one can be open at a time. The page in general utilizes Bootstrap 3 in order to be more responsive on various screen sizes.
