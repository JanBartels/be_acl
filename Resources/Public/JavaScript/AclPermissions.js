/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: Tx/BeAcl/AclPermissions
 * Javascript functions regarding the permissions acl module
 */
define(['jquery','TYPO3/CMS/Beuser/Permissions','TYPO3/CMS/Backend/Notification'], function($,Permissions,Notification) {

	var ajaxUrl = TYPO3.settings.ajaxUrls['user_access_permissions'];
	
	var AclPermissions = {
		options: {
			containerSelector: '#PermissionControllerEdit'
		}
	};
	
	var newACLs = new Array();
	var currentACLs = new Array();
	var editAclRowTpl;
	
	AclPermissions.getEditAclRowTpl = function() {
		if(!editAclRowTpl) {
			editAclRowTpl = $('#tx_beacl-edit-acl-row-template').html();
		}
		return editAclRowTpl;
	}
	/**
	* generates new hidden field
	*
	* @param name of field
	* @param value of field
	*/
	AclPermissions.createNewHiddenField = function(name, value) {
		var hiddenFields = document.getElementById('insertHiddenFields');
		var hiddenStore = document.createElement('input');
		hiddenStore.setAttribute('type', 'hidden');
		hiddenStore.setAttribute('value', value);
		hiddenStore.setAttribute('name', name);
		hiddenFields.appendChild(hiddenStore);
	}
	
	/**
	* create new ACL ID
	*/
   AclPermissions.getNewId = function() {
	   return 'NEW' + Math.round(Math.random()*10000000);
   }
	
	/**
	* add ACL
	*/
	AclPermissions.addACL = function() {
		var $container = $(AclPermissions.options.containerSelector);
		var pageID = $container.data('pageid');
		var ACLid = AclPermissions.getNewId();
		// save ACL ID in the new ACLs array
		newACLs.push(ACLid);
		// Create table row 
		var tableRow = AclPermissions.getEditAclRowTpl().replace(/###uid###/g,ACLid);
		// append line to table
		$('#typo3-permissionMatrix tbody').append(tableRow);
	};
	
	AclPermissions.removeACL = function(id) {
		var $tableRow = $('#typo3-permissionMatrix tbody').find('tr[data-acluid="'+ id +'"]');
		if($tableRow.length) $tableRow.remove();
	}
/**
	 * Group-related: Set the new group by executing an ajax call
	 *
	 * @param {Object} $element
	*/
	AclPermissions.deleteACL = function($element) {
		var $container = $(AclPermissions.options.containerSelector),
			pageID = $container.data('pageid'),
			id = $element.data('acluid');
		
		// New ACL - simply remove ACL from table
		if(isNaN(id)) {
			AclPermissions.removeACL(id);
			return;
		} 
		// Existing ACL - send delete request
		$.ajax({
			url: ajaxUrl,
			type: 'post',
			dataType: 'html',
			cache: false,
			data: {
				'action': 'delete_acl',
				'page': pageID,
				'acl': id
			}
		}).done(function(data) {
			// Remove from table
			AclPermissions.removeACL(id);
			// Show notification
			var title = data.title || 'Success';
			var msg = data.message || 'ACL deleted';
			Notification.success(title,msg,5);
		}).fail(function(jqXHR, textStatus, error) {
			Notification.error(null,error);
		});
	};
	
	/**
	* update user and group information
	*
	* @param ACLid - ID of ACL
	* @param objectId - Selected object id
	*/
	AclPermissions.updateUserGroup = function (ACLid,typeVal,objectId) {
		objectId = objectId || 0;
		var $container = $(AclPermissions.options.containerSelector),
			pageID = $container.data('pageid'),
			type = (typeVal == 1) ? 'group' : 'user';
	   
		// get child nodes of user/group selector
		var $selector = $('select[name=tx_beuser_system_beusertxpermission\\[data\\]\\[pages\\]\\['+pageID+'\\]\\[perms_'+type+'id\\]]');

		// delete current object selector options
		var $objSelector = $('select[name=tx_beuser_system_beusertxpermission\\[data\\]\\[tx_beacl_acl\\]\\[' + ACLid + '\\]\\[object_id\\]]');
		$objSelector.empty();

		// set new options on object selector
		var $option, $clonedOption;
		$selector.children().each(function() {
			// Filter out values without IDs
			$option = $(this);
			if($option.val() > 0 && $option.text() != '_cli_lowlevel') {
				$clonedOption = $option.clone().removeAttr('selected').appendTo($objSelector);
			}
		});
   };

	/**
	 * initializes events using deferred bound to document
	 * so AJAX reloads are no problem
	 */
	AclPermissions.initializeEvents = function() {
		// Select user or group
		$(AclPermissions.options.containerSelector)
			.on('change', '.tx_beacl-edit-type-selector', function(evt) {
				evt.preventDefault();
				var $el = $(evt.target);
				AclPermissions.updateUserGroup($el.data('acluid'),$el.val(),0);
			})
			.on('click', '.tx_beacl-addacl', function(evt) {
				evt.preventDefault();
				AclPermissions.addACL();
			})
			.on('click', '.tx_beacl-edit-delete', function(evt) {
				evt.preventDefault();
				AclPermissions.deleteACL($(this));
			})
			.find('.tx_beacl-edit-acl-row').each(function() {
				var acluid = $(this).data('acluid');
				if(acluid) {
					Permissions.setCheck("check[perms_acl_" + acluid + "]","tx_beuser_system_beusertxpermission[data][tx_beacl_acl][" + acluid + "][permissions]");
					currentACLs.push(acluid);
				}
			});

	};

	$(AclPermissions.initializeEvents);


	return AclPermissions;
});
