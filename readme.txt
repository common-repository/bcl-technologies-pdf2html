=== BCL Technologies PDF2HTML ===
Contributors: @bcltechnologies
Tags: pdf, html, convert, conversion
Requires at least: 4.4
Tested up to: 4.7
Stable tag: 1.0.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Include a PDF file as searchable HTML.

== Description ==

To include a PDF as searchable HTML on your site, convert and upload your PDF file as HTML in two easy steps!

1. Choose your output option: full HTML or body only with inline CSS
2. Click 'Upload' and you're done!

The uploaded document is sent to our server where the conversion processing takes place (hosted by BCL Technologies). We return the final output back to WordPress to create a [Page](https://codex.wordpress.org/Pages) with your content.  By uploading a document, you agree to our [Terms & Conditions](http://www.pdfonline.com/popups/terms.htm).

To see the difference between "full HTML" or "Inline CSS", refer to the FAQ below.

**Features**
* Inline CSS – Note: The resulting HTML positions elements exactly as they look in the original PDF.  As such, the output may differ depending on the current Theme’s CSS.

== Installation ==

1. Upload the `bcl-technologies-pdf2html` folder to your `/wp-content/plugins/` directory or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.


== Frequently Asked Questions ==

= What is the difference between full HTML or body only with inline CSS? =

Full HTML – This option generates a full HTML file of your uploaded PDF that includes standard `<head>` and `<style>` stags. These converted documents will be stored in the default Uploads directory of your WordPress installation.

Inline CSS – This option does not generate a full HTML file, rather it inlines all the styles and yields only the content that would go between the `<body>` tags of a full HTML file.  

= Where can I find my converted document? =
If the full HTML option is chosen, you can find your document in the easyConverter5 directory inside your Uploads folder. If we are unable to create this directory, the default Uploads folder is used instead.

For the inline CSS option, the content is within the [Page](https://codex.wordpress.org/Pages) that was created.

= Can I convert an encrypted or password protected PDF? =
We respect the PDF author’s restrictions. You should get the password and unlock the document. 

== Screenshots ==

1. Choose your document.
2. Upload.
3. Page with link to Full HTML
4. Full HTML file
5. Inline CSS

== Changelog ==

= 1.0 =
* Initial version of the plugin.

