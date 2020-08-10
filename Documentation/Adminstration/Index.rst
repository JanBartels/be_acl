.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _adminstration:

Adminstration
=============

Nothing to administer. Just install the extension.

When installing the extension, you have the possibility to switch the current “old” permission
management on or off. When switching it off, remember that you have to add some ACLs for your user
groups, or nobody except administrators can edit the data anymore.

There is a new option for the extension in the extension manager called enableFilterSelector. If you
enable this option, you will see a box where you can select the visible users/groups in the
permission matrix to make this view easier to handle.

The extension is for TYPO3 3.8 and up, because there are two hooks necessary which are only in the
core in these versions and up. If you use an older version, you need to patch the core.

The hooks used are the following:

::

    $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['calcPerms']
    $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['getPagePermsClause']


be_acl supports the Caching Framework for storing the access lists for editors to increase the
performance. The default entries are:

::

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_be_acl_timestamp'] = array(
        'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend',
        'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\SimpleFileBackend',
    );

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_be_acl_permissions'] = array(
        'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend',
        'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\SimpleFileBackend',
    );

.. toctree::
    :maxdepth: 2
    :titlesonly:

    TipsAndTricksForLarge/Index
