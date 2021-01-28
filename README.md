## Laravel test
#### Task:
Use the latest version of Laravel to send multiple emails asynchronously over API

#### Overview: 
Build a simple API that supports the sending route
Build a Mail object which accepts email, subject, body, attachments
Make sure that emails are sent asynchronously, i.e. not blocking the send request
Test the route

#### Used API routes:

POST api/send

The token is used as a URI parameter in the request api_token={{your_api_token}}


#### Goal:
The primary goal is for the functionality to work as expected. The idea is to spend about 4 working hours on it, maximum 8 working hours. 


#### Minimum requirements:
- &#x2611; Have an endpoint as described above that accepts an array of emails, each of them having subject, body, base64 attachments (could be many or none) and the email address where is the email going to
- &#x2611; Attachments, if provided, need to have base64 value and the name of the file
- &#x2611; Build a mail using a standard Laravel functions for it and default email provider (the one that is easiest for you to setup)
- &#x2611; Build a job to dispatch email, use the default Redis/Horizon setup
- &#x2611; Write a unit test that makes sure that the job is dispatched correctly and also is not dispatched if there’s a validation error
### Bonus requirements:
- &#x2611; Have an endpoint api/list that lists all sent emails with email, subject, body and downloadable attachments
- &#x2611; Unit test the above mentioned route (test for expected subject/body/attachment name)

##### Project prerequisites
- PHP 7.4
- Redis is installed

#####Handy commands:
- php artisan make:middleware ApiToken
- php artisan queue:table
- sudo systemctl enable redis-server
- sudo service redis-server start
- php vendor/phpunit/phpunit/phpunit tests/Feature/MailTest.php 

#### Postman Collection Link
https://www.getpostman.com/collections/230d26809c067d48c1cb
