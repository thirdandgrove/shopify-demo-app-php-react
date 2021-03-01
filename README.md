# shopify-demo-app-php-react
A demo embedded Shopify admin application highlighting integrating Shopify PHP middleware and React components.

## Installation
You'll need a working Linux environment with Redis and a web server. This has been tested using Vagrant / Virtualbox with Apache and PHP 7.3. The environment was setup to be accessed locally at https://shopify-demo.local but the actual domain is not important.

Your development environment must have [Composer](https://getcomposer.org) and [Yarn](https://yarnpkg.com) installed.

You must have a [Shopify Partner](https://www.shopify.com/partners) account and a development store for testing. 
 
* Clone the repository. Checkout `main` branch.
* Copy .env to .env.local
* Run `composer install`
* Run `yarn install`
* Run `yarn encore dev`
* Configure your web server to point to the `public` directory. 
* Access the application with a browser. If all is well, it should return a blank page except for the words "Bad request". This is because it is not a signed request from Shopify.
* Shopify requires requests to be https. If you use a self signed certificate, make sure you go through the "this is unsafe" step on your browser before attempting the next steps. Remember this too while testing if you clear your browser cache or the app suddenly stops working. It's not always obvious what's happening in the iframe.
Alternatively, use a service such as [ngrok](https://ngrok.com). This will provide a public URL like https://abc123xyz.ngrok.io with a properly signed certificate. Ngrok will come in handy if you add webhooks.
* Login to your Shopify Partner account and create a "Custom" app with the following settings:  
  * `App URL` The home URL of your app. e.g. https://shopify-demo.local  
  * `Allowed redirection URL(s)` The full URL to `/shopify/oauth-callback`. e.g., `https://shopify-demo.local/shopify/oauth-callback`
* Click on `Extensions` and create an "Admin link" with the following settings:  
  * Link label: `Edit prices demo`  
  * Link target URL: `https://example.com/edit-product`. The domain does not matter so literally `example.com` works here.  
  * Page to show link: `Product details`
* Copy the `API key` and `API secret key` to .env.local `SHOPIFY_API_KEY` and `SHOPIFY_API_SECRET`.
* Click the `Select store` button under `Test your app`.
* Select your development store and click `Install app`.
* If all is well, you should be redirected to the store with a prompt to install the app.
* Click install. When it completes, you will be redirected to the `Apps` area of the store. Click on the new app and it should simply say "Home page".
* Go to `Products` and click on a product.
* The `More Actions` menu should contain a link to the app. Click that to go to the app. You should be able to edit prices, save and return to the product detail page.

For active development, run `yarn encore dev --watch` to watch for changes and automatically recompile.  
## Uninstall
For now, if you uninstall the app from the store, you'll need to delete the key from Redis if you want to reinstall it. From the Linux command line: 
* `redis-cli`
* `del demo|mystore.myshopify.com|shopify_access_token` where `mystore` is the store name.
* `quit`

Todo: Implement subscribing to a webhook to do this automatically.
