casper
======

C'est l'interface client de rechargement.

## Mode maintenance

Pour passer en mode maintenance, ajouter ceci dans la config d'Apache (.htaccess ou VirtualHost) :

    <IfModule mod_rewrite.c>
    Options +FollowSymLinks
    RewriteEngine on
    RewriteCond %{REQUEST_URI} !/maintenance.html$ [NC]
    RewriteCond %{REQUEST_URI} !\.(jpe?g?|png|gif|css) [NC]
    RewriteRule .* /payutc/maintenance.html [R=302,L]
    </IfModule>