<h1 align="center">SuluRedirectBundle</h1>

<p align="center">
    <a href="https://sulu.io/" target="_blank">
        <img width="30%" src="https://sulu.io/uploads/media/800x/00/230-Official%20Bundle%20Seal.svg?v=2-6&inline=1" alt="Official Sulu Bundle Badge">
    </a>
</p>

<p align="center">
    <a href="https://github.com/sulu/SuluRedirectBundle/blob/2.x/LICENSE" target="_blank">
        <img src="https://img.shields.io/github/license/sulu/SuluRedirectBundle.svg" alt="GitHub license">
    </a>
    <a href="https://github.com/sulu/SuluRedirectBundle/releases" target="_blank">
        <img src="https://img.shields.io/github/tag/sulu/SuluRedirectBundle.svg" alt="GitHub tag (latest SemVer)">
    </a>
    <a href="https://github.com/sulu/SuluRedirectBundle/actions" target="_blank">
        <img src="https://img.shields.io/github/workflow/status/sulu/SuluRedirectBundle/Test%20application.svg?label=test-workflow" alt="Test workflow status">
    </a>
    <a href="https://github.com/sulu/sulu/releases" target="_blank">
        <img src="https://img.shields.io/badge/sulu%20compatibility-%3E=2.0-52b6ca.svg" alt="Sulu compatibility">
    </a>
</p>
<br/>

The SuluRedirectBundle adds simple but powerful capabilities for managing redirects to Sulu’s administration interface
and allows content managers to manage redirects without any knowledge of web servers.

<br/>
<p align="center">
    <img width="80%" src="https://sulu.io/uploads/media/800x@2x/02/342-SuluRedirectBundle%20Slideshow.gif?v=1-0" alt="SuluRedirectBundle Slideshow">
</p>
<br/>

The SuluRedirectBundle is compatible with Sulu **starting from version 2.0**. Have a look at the `require` section in
the [composer.json](https://github.com/sulu/SuluRedirectBundle/blob/2.x/composer.json) to find an
**up-to-date list of the requirements** of the bundle.

## 🚀&nbsp; Installation and Documentation

Execute the following [composer](https://getcomposer.org/) commands to add the bundle to the dependencies of your
project:

```bash
composer require sulu/redirect-bundle
```

Afterwards, visit the [bundle documentation](https://github.com/sulu/SuluRedirectBundle/blob/2.x/Resources/doc/README.md) to
find out **how to set up and configure the SuluRedirectBundle** to your specific needs.

## 💡&nbsp; Features

### Importing redirects

One of the great features of this bundle is the ability to import redirects from a CSV file.
The most simplified file just contains two columns, `source` and `target`.
Of course, all the other options like `statusCode`, `sourceHost`, and `enabled` can also be set in the import file.

It’s also possible to override existing redirects with an import; you just have to set the same value for `source`.

### Enabling and disabling redirects

Sometimes it’s necessary to prepare redirects which are not ready yet, especially if you have a large number of redirects.
Of course there’s a solution for that — toggler in the toolbar to enable or disable redirects quickly.
This allows you to import a large number of disabled redirects and then check them in the administration interface,
before enabling them when needed.

### Different statuses

The SuluRedirectBundle comes with three different statuses to be used for redirects:

- **301** Moved permanently
- **302** Moved temporarily
- **410** Gone

Those status codes are explained in detail [here](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status).

### Automatic `Gone` redirects

This bundle also adds the possibility to automatically create redirects with status `410 Gone`, if a page or a entity with a route has been removed. This is very useful, because now search engines know, that that page has been deleted.


## ❤️&nbsp; Support and Contributions

The Sulu content management system is a **community-driven open source project** backed by various partner companies.
We are committed to a fully transparent development process and **highly appreciate any contributions**.

In case you have questions, we are happy to welcome you in our official [Slack channel](https://sulu.io/services-and-support).
If you found a bug or miss a specific feature, feel free to **file a new issue** with a respective title and description
on the the [sulu/SuluRedirectBundle](https://github.com/sulu/SuluRedirectBundle) repository.

## 📘&nbsp; License

The Sulu content management system is released under the terms of the [MIT License](LICENSE).
