## PING TOOL

## Installation Guide
### Requiremnets
* `git` (https://git-scm.com/downloads)
* `PHP` >= `7.3` and `PHP extensions`: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath (Don't worry about them if you are installing in XAMPP environment). Recomendation:
    * Windows => `Laragon` (https://laragon.org/download/)
    * MacOS => `MAMP` (https://www.mamp.info/en/mamp/mac/)
    * Linux => `XAMPP` (https://www.apachefriends.org/download.html) or you can install it natively 
* `Composer v2.x` (https://getcomposer.org/)
* `NodeJS v10.x` (https://nodejs.org/en/download/)
* Note : This application does not require a database setup.

### How to Install
1. Clone this Repository
```bash 
$ git clone https://github.com/rachmadep/ping-tool.git

$ cd ping-tool
```
2. Run `composer install` for installing PHP library
```bash 
$ composer install
```
3. Run `npm install` for installing JavaScript library
```bash 
$ npm install
```
4. Run `php artisan serve` for running this application
![image](https://user-images.githubusercontent.com/15728759/127322445-c17ea0fb-a278-42b4-89cb-a0a19faabb95.png)

5. Open browser and access `http://127.0.0.1:8000`
![image](https://user-images.githubusercontent.com/15728759/127322744-a92f9ebc-25ad-4a10-a71d-e7582566b954.png)

## Build With
<p align="center">
    <a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300"></a>
    <a href="https://vuejs.org/" target="_blank"><img src="https://user-images.githubusercontent.com/15728759/127324008-38cfe36f-f15e-453e-8ae5-e666c67a11d0.png" width="100"></a>
</p>
