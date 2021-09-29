README
======

What is PSF?
------------

PSF는 PHP Simple Framework 로 간단한 파일(configure.json) 설정 만으로 다중 사이트를 운영할수 있게 도와주는 프레임워크 입니다.


Features
--------

PSF supports the following:

* *PHP 5.3 이상에서 사용 가능합니다.  

Requirements
------------

requires the following:

* PHP 5.3 or higher
* Composer - Dependency Management for PHP
* mysqlilib, https://github.com/jonathanbak/mysqlilib

**Note:**
php composer 를 설치하고 PSF를 추가하면 자동으로 의존성 라이브러리들을 설치합니다.

Installation
------------

1. Download and install Composer by following the [official instructions](https://getcomposer.org/download/).
2. Create a composer.json defining your dependencies. Note that this example is
a short version for applications that are not meant to be published as packages
themselves. To create libraries/packages please read the
[documentation](https://getcomposer.org/doc/02-libraries.md).

    ``` json
    {
        "require": {
            "jonathanbak/psf":"~1.1"
        }
    }
    ```

3. Run Composer: `php composer.phar install`

Start First Project
-------------
터미널에서 아래 스크립트를 실행후 데이터를 입력하시면 자동으로 폴더가 구성됩니다.
```shell
$ php ./vendor/jonathanbak/psf/bin/init.php 
Create database configuration file [N/y]?y
Input db file name (domain name) : sample.com
Input db host : 127.0.0.1
Input db user : test
Input db password : testpassword
Input database name : db_test 
Input db alias name : dbalias
Create new db..
.../config/db/sec.wendybook.loc.json

Create site configuration file [N/y]?y
Input site namespace : SampleSite
Input site domain : www.sample.com
Input db file name : sample.com
Create new site..
OK.
```


Folder Structure
-------------------

PSF 사용시 추천하는 폴더 구조는 아래와 같습니다. 

    .
    ├── app
    │   └── com.example         # example.com 사이트 루트 폴더
    │       ├── _tmp            # 임시폴더, 캐쉬파일과 로그 생성
    │       ├── controllers     # URL에서 접근하는 controller 파일
    │       ├── models          # 모델 파일, 주요 로직
    │       └── views           # View 폴더
    │           ├── css             # css 파일
    │           ├── image           # images 파일
    │           ├── js              # javascript 파일
    │           └── tpl             # tpl 파일 (html 파일)
    ├── config              # 설정 파일
    │   ├── db              # DB 정보 설정 파일
    │   └── site            # 사이트 설정 파일
    ├── html                # 실제 웹서버의 DOCUMENT_ROOT
    └── vendor              # Composer 라이브러리 폴더
    
**app 폴더 하위 구조**는 *config/site/usersiteurl.json 파일안에서 별도 정의가 가능합니다.*

**app 폴더 상위 구조**는 *configure.json 파일 에서 별도 정의가 가능합니다.*