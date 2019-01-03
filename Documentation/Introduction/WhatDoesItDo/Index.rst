.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _introduction-what-does-it-do:

What does it do?
----------------

Until now, the access scheme for the TYPO3 backend was the traditional User-Group-World scheme known
from Unix. This, however, is not sufficent when more complex permissions are needed, for example
when wanting to apply different permissions to different groups on the same page. Another issue in
the current permission system is that the page owners/groups are set to the user and the main group
of that user who creates the page.

This are the two scenarios the ACLs want to solve. It is now possible to define on a per-group and
per-user basis for pages which permissions are active. ACLs allow pages to have permissions for more
than one user/group on a page.

Furthermore, ACLs can work recursively, meaning an ACL doesn't need to be copied to all subpages
when it should apply there as well. This makes handling permissions easier.
