<?php

declare(strict_types=1);

final class PostCreateProjectCommand
{
    public static function showMessage(): void
    {
        $str = <<<EOF
                _                 _     
               | |               (_)    
  _ __ ___  ___| |_    __ _ _ __  _     
 | '__/ _ \/ __| __|  / _` | '_ \| |    
 | | |  __/\__ \ |_  | (_| | |_) | |    
 |_|  \___||___/\__|  \__,_| .__/|_|    
                           | |          
      _ _                  |_|          
     | (_)                 | |          
  ___| |_ _ __ ___    _ __ | |__  _ __  
 / __| | | '_ ` _ \  | '_ \| '_ \| '_ \ 
 \__ \ | | | | | | | | |_) | | | | |_) |
 |___/_|_|_| |_| |_| | .__/|_| |_| .__/ 
                     | |         | |    
                     |_|         |_|    
*************************************************************
Project: https://github.com/FernandoCalmet/rest-api-slim-php-sql
*************************************************************
Successfully created project!
Get started with the following commands:
$ cd [my-api-name]
$ composer restart-db
$ composer test
$ composer start
(P.S. set your MySQL connection in .env file)
Thanks for installing this project!
Now go build a cool RESTful API ;-)
EOF;
        echo $str;
    }
}

PostCreateProjectCommand::showMessage();