<div><img src="./doc/images/main_banner.png" alt="Euros main banner" /></div>

# Euros
This application has the function of recording attendance and leaving work on a web page and listing it by month.
It includes a function to copy records including memos from the list state to the clipboard in TSV-like format.
It also has a simple authentication function, so it is possible for multiple people to have individual data while sharing it on a single server.

### Origin of the Project Name
The name of this project, "Euros," is taken from the Greek god of wind, especially the east wind.

## Usage
1. Clone the repository:
```bash
$ git clone https://github.com/laplace-p-s/Euros.git
```
2. Install the required dependencies:
```bash
$ composer install
$ npm install
```
3. Run the application:
```bash
$ npm run build
```
4. Set up your web server
5. Set up your database
6. Set .env file:
```bash
$ cp .env.example .env
$ php artisan key:generate
```
7. Describe the configuration in the .env file
8. Set Database:
```bash
$ php artisan migrate
```
9. Done!  

*Refer to the Laravel documentation for detailed settings in the env file.

## Technologies Used
- Language: PHP 8.0
- Framework: Laravel 9.42.2
- Library: tailwindCSS 3.1.0, jQuery 3.6.3

## Contributing
- [Code of conduct](./CODE_OF_CONDUCT.md)

## License
MIT License
