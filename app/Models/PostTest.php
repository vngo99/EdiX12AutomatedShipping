<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class PostTest extends Model
{
   /*
     *
    DROP TABLE IF EXISTS `post_tests`;
    CREATE TABLE `post_tests` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(80)  NOT NULL,
        `data` text default null,
        `note` text default null,
        `updated_at` datetime NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    */

    protected $connection = 'mysql';
    use HasFactory;
    protected $fillable = [
        'name',
        'data',
        'note'
     
    ];

    

    
}
