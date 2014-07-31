var global_newACLs = new Array();
var global_currentACLs = new Array(); // stores already saved ACLs

/**
 * generates new hidden field
 *
 * @param name of field
 * @param value of field
 */
function createNewHiddenField (name, value) {
	var hiddenFields = document.getElementById('insertHiddenFields');
	var hiddenStore = document.createElement('input');
	hiddenStore.setAttribute('type', 'hidden');
	hiddenStore.setAttribute('value', value);
	hiddenStore.setAttribute('name', name);
	hiddenFields.appendChild(hiddenStore);
}

/**
 * deletes an ACL
 *
 * @param ID of ACL
 */
function deleteACL (id) {
	if(isNaN(id)) {
			// delete ACL by removing it from the DOM tree
		var deleteLine = document.getElementsByName('data[tx_beacl_acl][' + id + '][type]')[0].parentNode.parentNode;
		document.getElementsByName('data[tx_beacl_acl][' + id + '][type]')[0].parentNode.parentNode.parentNode.removeChild(deleteLine);
		var deleteLine2 = document.getElementsByName('data[tx_beacl_acl][' + id + '][permissions]')[0];
		document.getElementsByName('data[tx_beacl_acl][' + id + '][permissions]')[0].parentNode.removeChild(deleteLine2);
		var deleteLine3 = document.getElementsByName('data[tx_beacl_acl][' + id + '][pid]')[0];
		document.getElementsByName('data[tx_beacl_acl][' + id + '][pid]')[0].parentNode.removeChild(deleteLine3);
	} else {
			// delete by filling the cmdMap
		var hiddenFields = document.getElementById('insertHiddenFields');
		var hiddenDeleteCMDmap = document.createElement('input');
		hiddenDeleteCMDmap.setAttribute('type', 'hidden');
		hiddenDeleteCMDmap.setAttribute('name', 'cmd[tx_beacl_acl][' + id + '][delete]');
		hiddenDeleteCMDmap.setAttribute('value', 1);
		hiddenFields.appendChild(hiddenDeleteCMDmap);

		document.editform.submit.click();
	}
}

/**
 * update user and group information
 *
 * @param ID of ACL
 * @param selected entry
 */
function updateUserGroup (ACLid,selectedEntry) {
	var pageID = document.getElementsByName('pageID')[0].value;
	var typeSelector = document.getElementsByName('data[tx_beacl_acl][' + ACLid + '][type]')[0];

	// get child nodes of user/group selector
	if(typeSelector.value == 0) {
		// USER
		var childNodes = document.getElementsByName('data[pages]['+pageID+'][perms_userid]')[0].childNodes;
	} else {
		var childNodes = document.getElementsByName('data[pages]['+pageID+'][perms_groupid]')[0].childNodes;
	}

	// delete current nodes
	var objId = document.getElementsByName('data[tx_beacl_acl][' + ACLid + '][object_id]')[0];
	var length = objId.childNodes.length;
	for(var i=0; i < length;i++) {
		objId.removeChild(objId.firstChild);
	}

	// set new nodes
	for(var i=0;i<childNodes.length;i++) {
		var tmp = childNodes[i].cloneNode(true);
		document.getElementsByName('data[tx_beacl_acl][' + ACLid + '][object_id]')[0].appendChild(tmp);
	}

	if(arguments.length == 2) {
		document.getElementsByName('data[tx_beacl_acl][' + ACLid + '][object_id]')[0].value = selectedEntry;
	}
}

/**
 * create new ACL ID
 */
function getNewID () {
		// CREATE ID for new ACL
	var rand = Math.random()*10000000;
	rand = Math.round(rand);
	var ACLid = 'NEW' + rand;
	return ACLid;
}

/**
 * add ACL
 */
function addACL () {
		// CREATE ID for new ACL
	var ACLid = getNewID();
		// save ACL ID in the new ACLs array
	global_newACLs[global_newACLs.length] = ACLid;


	var tableRow = document.createElement("tr");

	var tableCells = Array(8);
	var variousObjects = Array();
	var selectorBoxes = Array(6);

		// first table cell with selectors
	tableCells[0] = document.createElement("td");
	tableCells[0].className = 'bgColor2';
	tableCells[0].align = 'right';

		if (navigator.appName.indexOf("Explorer") != -1)	{
			variousObjects[1] = document.createElement('<select name="data[tx_beacl_acl][' + ACLid + '][type]">');

		} else {
			variousObjects[1] = document.createElement('select');
			variousObjects[1].setAttribute("name", 'data[tx_beacl_acl][' + ACLid + '][type]');
		}
		variousObjects[1].onchange = function() { updateUserGroup(ACLid) };

			variousObjects[11] = document.createElement('option');
			variousObjects[11].value = 1;
				variousObjects[111] = document.createTextNode('Group');
				variousObjects[11].appendChild(variousObjects[111]);
			variousObjects[12] = document.createElement('option');
			variousObjects[12].value = 0;
				variousObjects[121] = document.createTextNode('User');
				variousObjects[12].appendChild(variousObjects[121]);
			variousObjects[1].appendChild(variousObjects[11]);
			variousObjects[1].appendChild(variousObjects[12]);

		if (navigator.appName.indexOf("Explorer") != -1)	{
			variousObjects[2] = document.createElement('<select name="data[tx_beacl_acl][' + ACLid + '][object_id]">');
		} else {
			variousObjects[2] = document.createElement('select');
			variousObjects[2].setAttribute("name", 'data[tx_beacl_acl][' + ACLid + '][object_id]');
		}
	tableCells[0].appendChild(variousObjects[1]);
	tableCells[0].appendChild(variousObjects[2]);
	tableRow.appendChild(tableCells[0]);


		// permission table cells
	for(var i = 1; i <= 6;i++) {
		tableCells[i] = document.createElement("td");
		var id = 0;
		switch(i) {
			case 1: id = 1; break;
			case 2: id = 5; break;
			case 3: id = 2; break;
			case 4: id = 3; break;
			case 5: id = 4; break;

			case 6:
				if (navigator.appName.indexOf("Explorer") != -1)	{
					selectorBoxes[i-1] = document.createElement('<input name="data[tx_beacl_acl][' +  ACLid + '][recursive]">');
				} else {
					selectorBoxes[i-1] = document.createElement('input');
					selectorBoxes[i-1].setAttribute("name", 'data[tx_beacl_acl][' +  ACLid + '][recursive]');
				}
				selectorBoxes[i-1].setAttribute("type", 'checkbox');
				selectorBoxes[i-1].value = 1;
				tableCells[i].appendChild(selectorBoxes[i-1]);
				break;
		}
		if(id != 0) {
			if (navigator.appName.indexOf("Explorer") != -1)	{
				selectorBoxes[i-1] = document.createElement('<input name="check[perms_acl_' +  ACLid + ']['+id+']">');
			} else {
				selectorBoxes[i-1] = document.createElement('input');
				selectorBoxes[i-1].setAttribute("name", 'check[perms_acl_' +  ACLid + ']['+id+']');
			}
			selectorBoxes[i-1].setAttribute("type", 'checkbox');
			selectorBoxes[i-1].onclick = function() { checkChange('check[perms_acl_'+ACLid+']', 'data[tx_beacl_acl]['+ACLid+'][permissions]') };
			tableCells[i].appendChild(selectorBoxes[i-1]);
		}
		tableRow.appendChild(tableCells[i]);
	}

		// delete ACL link
	variousObjects[31] = document.getElementById("templateDeleteImage").cloneNode(true);
	variousObjects[31].style.display = 'inline';
	variousObjects[30] = document.createElement('a');
	variousObjects[30].href = 'javascript:deleteACL("'+ACLid+'")';
	variousObjects[30].onclick = function() { deleteACL(ACLid) };
	variousObjects[30].appendChild(variousObjects[31]);

	tableCells[7] = document.createElement("td");
	tableCells[7].appendChild(variousObjects[30]);
	tableRow.appendChild(tableCells[7]);

		// append line to table
	document.getElementById('typo3-permissionMatrix').getElementsByTagName('tbody')[0].appendChild(tableRow);

		// hidden fields
	var hiddenFields = document.getElementById('insertHiddenFields');
	if (navigator.appName.indexOf("Explorer") != -1)	{
		var hiddenACLstore = document.createElement('<input name="data[tx_beacl_acl][' + ACLid + '][permissions]">');
	} else {
		var hiddenACLstore = document.createElement('input');
		hiddenACLstore.name = 'data[tx_beacl_acl][' + ACLid + '][permissions]';
	}
	hiddenACLstore.setAttribute('type', 'hidden');
	hiddenACLstore.setAttribute('value', 0);

	hiddenFields.appendChild(hiddenACLstore);

	if (navigator.appName.indexOf("Explorer") != -1)	{
		var hiddenPIDstore = document.createElement('<input name="data[tx_beacl_acl][' + ACLid + '][pid]">');
	} else {
		var hiddenPIDstore = document.createElement('input');
		hiddenPIDstore.setAttribute('name', 'data[tx_beacl_acl][' + ACLid + '][pid]');
	}
	hiddenPIDstore.setAttribute('type', 'hidden');
	hiddenPIDstore.setAttribute('value', document.getElementsByName('pageID')[0].value);

	hiddenFields.appendChild(hiddenPIDstore);

		// update user and groups for new ACLs
	updateUserGroup(ACLid);
}