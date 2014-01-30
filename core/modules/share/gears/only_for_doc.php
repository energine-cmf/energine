<?php
/**
 * @mainpage Energine CMF.
 * @tableofcontents
 *
 * @section about About.
 *
 * Energine is a content management system which allows to support web-applications (including websites) of any level of complexity. Energine is based on Energine CMF — a power full toolkit for web-application development using XML/XSLT transformations.
 *
 * Main features of Energine are:
 * - Multi-language support. Energine supports unbounded quantity of languages with ability to translate not only content of a site, but buttons, emails, captions too.
 * - %User's access delimitation. %User's access control system allows to edit user's rights to access and edit different parts of a website.
 * - Visual text editor. A built in WYSIWYG (what you see is what you get) editor is a handy tool to edit web site's content and preview it.
 * - Files. Common file storage allows to use one method to work with files in forms and with a help of text editor.
 * - Structure site management. Web site's structure represented as a tree. %User can add, edit and delete it's nodes to modify parts of a site.
 * - Shop module. Additional module which allows to create and use eShop.
 *
 * @section foreword Forewords.
 *
 * Since Energine 2.11.0 the project structure was changed.
 * Now the releases of Energine core can be stored in separate directory (for example <tt>/var/www/energine</tt>).
 * The projects are now no longer pull the kernel and modules over svn:externals, they connect required versions of core and external modules over configuration file.
 *
 * @section install Installation guide
 *
 * -# Create directory for core (for example <tt>/var/www/energine</tt>)
 * @code
mkdir /var/www/energine
@endcode
 * -# Download and extract Energine release
 *   - from Github https://github.com/energine-cmf/energine/releases
 *   - or get clone of our repository
 * @code
cd /var/www
git clone git@github.com:energine-cmf/energine.git
@endcode
 * -# Copy default starter
 * @code
cd /home/username/projects
cp -r /var/www/energine/starter www.mynewsite.com
@endcode
 * -# Create database.
 * <ol>
 *   <li>Import base site structure from <tt>sql/starter.structure.sql</tt>
 *   <li>Import stored procedures from <tt>sql/starter.routines.sql</tt>
 *   <li>Import system data from <tt>sql/starter.data.sql</tt>
 * </ol>
 * -# Configurations.
 * <ol>
 *   <li>Copy configuration file from <tt>configs/system.config.default.php</tt> to <tt>configs/system.config.username.php</tt>.
 *   <li>Make symlink from <tt>configs/system.config.username.php</tt> to <tt>htdocs/system.config.php</tt>.
 *   <li>Edit configuration file:
 *   <ol>
 *     <li>Set directory of the Energine core
 *     <li>Set correct database configurations
 *     <li>Set site domain
 *     <li>Set the modules, which will be accessed from the site (full path for each module)
 *     <li>Set your E-Mail
 *   </ol>
 * </ol>
 * -# Configure web-server: <tt>nginx + php_fpm</tt> or <tt>apache2 + mod_php</tt>
 *   -# @c nginx
 *   <ol>
 *     <li>Base config file is in <tt>jambalaya/.nginx.conf.example</tt>. You can copy this config into the directory @c conf.d of your nginx as conf file (for example <tt>www.mysite.com.conf</tt>).
 *     <li>Set your connection to @c php-fpm in <tt>upstream php-fpm</tt> block
 *     <li>Set correct @c server_name and listen port in @c server block
 *     <li>Set absolute path of the project into <tt>$www_folder</tt> variable in server block
 *     <li>Set permissions to write to the directory <tt>htdocs/uploads</tt>.
 *   </ol>
 *   -# @c apache2
 *   <ol>
 *     <li>Configure your VirtualHost if it is not yet done.
 *     <li>Base config file is in <tt>jambalaya/.htaccess</tt>. Copy it to <tt>www.mysite.com/htdocs/</tt>
 *     <li>Set the single directive @c RewriteBase. If the project is placed in some folder, not in site core, then the directive should be: <tt>RewriteBase /~username/some/folder/</tt>. If it placed in site core then: <tt>RewriteBase /</tt>
 *     <li>Set permissions to write to the directories: <tt>htdocs, htdocs/uploads, htdocs/core/modules</tt>.
 *   </ol>
 * -# Run http://www.your.project.address/setup/  If all is ok - you will have a working system copy based on Energine engine.
 * -# Develop your site
 * -# If you use @c apache2, remove the permissions to write to the directories: <tt>htdocs, htdocs/core/modules</tt>.
 * -# Turn off debug mode in configuration file.
 */


/**
 * @class Exception
 * @brief Native Exception class.
 * http://ua2.php.net/manual/en/class.exception.php
 */

/**
 * @interface Iterator
 * @brief Native Iterator interface.
 * http://ua2.php.net/manual/en/class.iterator.php
 */
/**
 * @fn current()
 * @memberof Iterator
 * @brief Get current element.
 * @return mixed
 *
 * http://php.net/manual/en/iterator.current.php
 */
/**
 * @fn next()
 * @memberof Iterator
 * @brief Move forward to next element
 *
 * http://php.net/manual/en/iterator.next.php
 */
/**
 * @fn key()
 * @memberof Iterator
 * @brief Return the key of the current element
 * @return mixed
 *
 * http://php.net/manual/en/iterator.key.php
 */
/**
 * @fn valid()
 * @brief Checks if current position is valid
 * @memberof Iterator
 * @return bool
 *
 * http://php.net/manual/en/iterator.valid.php
 */
/**
 * @fn rewind()
 * @brief Rewind the Iterator to the first element
 * @memberof Iterator
 * @return bool
 *
 * http://php.net/manual/en/iterator.rewind.php
 */


/**
 * @class IteratorAggregate
 * @brief Native IteratorAggregate interface.
 * http://ua2.php.net/manual/en/class.iteratoraggregate.php
 */
/**
 * @fn getIterator();
 * @memberof IteratorAggregate
 * http://php.net/manual/en/iteratoraggregate.getiterator.php
 */

/**
 * @class DateTime
 * @brief Native DateTime class.
 * http://php.net/manual/en/class.datetime.php
 */

/**
 * @class PDO
 * @brief Native PDO class.
 * http://php.net/manual/en/class.pdo.php
 */