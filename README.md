# LIBERSTORE
adalah website penjualan buku yang menggunakan framework `slim`

## Requirement

to use this web application you must provide at least `PHP 5.5`, so you can use `PHP 5.5` or Higher

## API Reference

Register [POST]
        
       /user/register
- Require param

   * username
   * email
   * password
   * retype password
       
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


Replace `[my-app-name]` with the desired directory name for your new application. You'll want to:

* Point your virtual host document root to your new application's `public/` directory.
* Ensure `logs/` is web writeable.

That's it! Now go build something cool.

