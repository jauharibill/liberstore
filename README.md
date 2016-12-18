# LIBERSTORE
liberstore is onlineshop that provide book that user can buy, this website built by framework `slim` . this website architect provide 3 point

* admin
* web desktop
* rest api

so you can colaborate/upgrade this website to mobile apps too. enjoy it dude!

## Requirement

to use this web application you must provide at least `PHP 5.5`, so you can use `PHP 5.5` or Higher

## API Reference

Register [POST]
        
       /user/register
- Require param

   * username
   * email
   * password
       
Login [POST]

       /user/login
- Require param

   * username
   * password

Get list books [GET]

       /user/get_book_list
       
Get data profile [POST] 
       
       /user/find_profile/{token}
       
Get detail book with token [GET]

       /user/get_detail_book/{token}/{code_book}
       
Update profile user [POST]
        
       /user/update_profile    
- Require param

   * fullname
   * phone
   * place
   * born
   * postal_code
   * token

Check profile [GET]
        
       /user/check_profile/{token}
       
Buy book [POST]

        /user/buy_book
- Require param

   * token
   * code_book

Get my book [GET]

        /user/get_mybook/{token}

Activate account [POST]
        
        /user/activate_account
- Require param

   * activation_code


## Install the Application

Clone Repository

       git clone [this repo]
    
Update Composer
   
       composer update
       
Run Server
    
       php -S localhost:8000 [enter]


That's it! Now go build something cool.

Regard

./billxcode
