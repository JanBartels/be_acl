.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _users-manual-recursive:

Recursive
^^^^^^^^^

The “recursive” flag needs special mentioning, because it works different compared to the
standard TYPO3 behavior.

In the above example (see the second screenshot), the pages “TimTaw-New” and “page3” have an
ACL applied which works recursively for group g1. “page1” has a single ACL, not working
recursively. This means on all subpages of page1, the ACL from TimTaw-New applies. In the above
example, there is also one ACL defined for g2.

As a default, the recursive ACL works for all pages under that page until another ACL for the same
user/group is found. This means that on page “test”, the ACL from “TimTaw-new” is applied.
On “page3” and subpage, the ACL from “timTaw-New” is not taken into account because the ACL
from “page3” is active there.

Because the ACL on “page1” is non-recursive, it overrides the ACL from “timTaw-new” for that
page, but not for subpages. On “subpage”, the ACL from “TimTaw-New” is applied. This makes
it possible to create schemes disallowing special things just for a certain “protected” page.
