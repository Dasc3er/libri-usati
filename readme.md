# Libri usati

Sito per la gestione della vendita di libri usati.

## Installazione manuale

```bash
npm install
php composer.phar update
php -r "copy('.env.example', '.env');"
php artisan vendor:publish --tag=laravelinstaller
npm run serve
```

Visitare il percorso del server e procedere alla configurazione.