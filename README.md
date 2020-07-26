# Oxid optimize

## Description

Minimize HTML.

JS, CSS files will cache by compile (scss) and minimize them 
and save in the same folder with a different filename. If the 
suffix is "now" it will use the current timestamp and at 
every page call the file will recreate. After some time, there
are many files.

The DB cache save all sql statments made by the list model. It 
will save in the tmp folder. If you use the suffix "now" it 
will create a new folder for every day. After some time, there
can be many folders.

This extension was created for Oxid 6.x.


## Install

1. Copy files into following directory

        source/modules/rs/optimize
        
2. Add following to composer.json on the shop root

        "autoload": {
            "psr-4": {
                "rs\\optimize\\": "./source/modules/rs/optimize"
            }
        },

3. Install dependencies

        composer require leafo/scssphp --no-update
        composer require matthiasmullie/minify --no-update
        composer update --no-plugins --no-scripts --no-dev --ignore-platform-reqs
    
4. Refresh autoloader files with composer.

        composer dump-autoload
        
5. Enable module in the oxid admin area, Extensions => Modules
6. Changes settings in the module itself

![](settings.png)

## Manual optimization

Add to .htaccess at the end of the file

        #cpOptimization module start
        <IfModule mod_deflate.c>
            AddOutputFilterByType DEFLATE text/html
            AddOutputFilterByType DEFLATE text/css
            AddOutputFilterByType DEFLATE text/javascript
            AddOutputFilterByType DEFLATE application/javascript
            AddOutputFilterByType DEFLATE application/x-javascript
            AddOutputFilterByType DEFLATE image/svg+xml
            AddOutputFilterByType DEFLATE application/javascript
            AddOutputFilterByType DEFLATE application/rss+xml
            AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
            AddOutputFilterByType DEFLATE application/x-font
            AddOutputFilterByType DEFLATE application/x-font-opentype
            AddOutputFilterByType DEFLATE application/x-font-otf
            AddOutputFilterByType DEFLATE application/x-font-truetype
            AddOutputFilterByType DEFLATE application/x-font-ttf
            AddOutputFilterByType DEFLATE application/x-javascript
            AddOutputFilterByType DEFLATE application/xhtml+xml
            AddOutputFilterByType DEFLATE application/xml
            AddOutputFilterByType DEFLATE font/opentype
            AddOutputFilterByType DEFLATE font/otf
            AddOutputFilterByType DEFLATE font/ttf
            AddOutputFilterByType DEFLATE image/x-icon
            AddOutputFilterByType DEFLATE text/plain
            AddOutputFilterByType DEFLATE text/xml
        </IfModule>
        <IfModule mod_headers.c>
            <FilesMatch "\.(eot|svg|ttf|woff|woff2)$">
                Header set Cache-Control "max-age=15552000‬, public"
            </FilesMatch>
            <FilesMatch "\.(ico|jpg|jpeg|png|gif|swf)$">
                Header set Cache-Control "max-age=15552000, public"
            </FilesMatch>
            <FilesMatch "\.(css|js)$">
                Header set Cache-Control "max-age=15552000, public"
            </FilesMatch>
            Header unset ETag
        </IfModule>
        FileETag None
        ServerSignature Off
        #cpOptimization module end
    