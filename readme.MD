media Connector
===============

Requirements:
-------------

**Jcrop**

see :
https://github.com/tapmodo/Jcrop

or check examples from

git clone git@github.com:tapmodo/Jcrop.git

or include the file in Resources\public\javascript 

**jquery**

tested with Version > 3.x



**extension: media_upload**

composer req fab/media-upload@^1.0

"fab/media-upload": "^1.0",

add to PSR4 
			"JVE\\JvMediaConnector\\": "http/typo3conf/ext/jv_media_connector/Classes"
			
			
What does this Extension do?
============================
Based on the extension media-upload it oferes a front end Plugin with theses options:
- List images of a frontend user 
- add a new Image
- resize the new uploaded Image
- delete image 
- create new File reference between this image and any other data.



			
			