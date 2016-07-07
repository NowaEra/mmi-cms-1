/**
 * Obsługa drzewka kategorii CMS
 */

var request = request || {};
//konfiguracja
var CATEGORYCONF = CATEGORYCONF || {};
//klucz do stanu drzewka
CATEGORYCONF.stateKey = 'cms-category-jstree';
//po jakim czasie ukryć message
CATEGORYCONF.msgDelay = 2500;

//zarządzanie kategoriami
$(document).ready(function () {
	//przeniesienie messengera
	$('ul#messenger').appendTo('#categoryMessageContainer').show();
	
	//odpalenie drzewka
	$('#jstree').jstree({
        'core': {
			'data': {
				'url': request.baseUrl + '/cmsAdmin/category/node',
				'data': function (node) {
					return {'parentId': node.id};
				}
			},
            'themes': {
                'name': 'default',
				'variant': 'small',
				'responsive' : false,
				'stripes' : true
            },
			'strings': {
				'New node': 'Nowa kategoria',
				'Loading ...': 'Ładowanie ...'
			},
			'multiple': false,
			'expand_selected_onload': true,
			'force_text': true,
            'check_callback': function (op) {
				if (op === 'delete_node') {
					return confirm("Czy na pewno usunąć kategorię?");
					}
				return true;
			}
        },
		'state' : {
			'key' : CATEGORYCONF.stateKey
		},
		'unique' : {
			'duplicate': function (name, counter) {
				return name + ' ' + counter;
			}
		},
		'types': {
			'#': { 'valid_children': ["root"] },
			'root': { 'valid_children': ["default", "inactive"], 'icon': request.baseUrl + '/resource/cmsAdmin/images/tree.png' },
			'default': { 'valid_children': ["default", "inactive"] },
			'inactive': { 'valid_children': ["default", "inactive"], 'icon': 'jstree-inactive' }
		},
		'contextmenu': {
			'items': function (node) {
				var tmp = $.jstree.defaults.contextmenu.items();
				delete tmp.ccp;
				tmp.create.label = "Utwórz podkategorię";
				tmp.rename.label = "Zmień nazwę";
				tmp.remove.label = "Usuń";
				if (this.get_type(node) === "root") {
					delete tmp.rename;
					delete tmp.remove;
					tmp.create.separator_after = false;
					tmp.create.label = "Utwórz kategorię bazową";
				} else {
					tmp.edit = {
						"separator_before": true,
						"separator_after": false,
						"label": "Edytuj",
						"action": function (data) {
							var inst = $.jstree.reference(data.reference);
							var node = inst.get_node(data.reference);
							CATEGORYCONF.editForm(node);
						}
					}
				}
				return tmp;
			}
		},
		'plugins' : [ "state", "unique", "types", "contextmenu", "dnd", "wholerow" ]
    })
	.on('delete_node.jstree', function (e, data) {
		CATEGORYCONF.hideMessage();
		$.post(request.baseUrl + '/cmsAdmin/category/delete', {'id': data.node.id})
			.done(function (d) {
				if (d.status) {
					$('#jstree').jstree('deselect_all');
					$('#jstree').jstree('select_node', data.parent);
				} else {
					data.instance.refresh();
				}
				CATEGORYCONF.showMessage(d);
			})
			.fail(function () {
				data.instance.refresh();
				CATEGORYCONF.showMessage({'error': 'Nie udało się usunąć kategorii'});
			});
	})
	.on('create_node.jstree', function (e, data) {
		CATEGORYCONF.hideMessage();
		$.post(request.baseUrl + '/cmsAdmin/category/create', {'parentId': data.node.parent, 'order': data.position, 'name': data.node.text})
			.done(function (d) {
				if (d.status) {
					data.instance.set_id(data.node, d.id);
					$('#jstree').jstree('deselect_all');
					$('#jstree').jstree('select_node', d.id);
				} else {
					data.instance.refresh();
				}
				CATEGORYCONF.showMessage(d);
			})
			.fail(function () {
				data.instance.refresh();
				CATEGORYCONF.showMessage({'error': 'Nie udało się utworzyć kategorii'});
			});
	})
	.on('rename_node.jstree', function (e, data) {
		CATEGORYCONF.hideMessage();
		$.post(request.baseUrl + '/cmsAdmin/category/rename', {'id': data.node.id, 'name': data.text})
			.done(function (d) {
				if (d.status) {
					data.node.text = d.name;
					$('#jstree').jstree('deselect_all');
					$('#jstree').jstree('select_node', d.id);
				} else {
					data.instance.refresh();
				}
				CATEGORYCONF.showMessage(d);
			})
			.fail(function () {
				data.instance.refresh();
				CATEGORYCONF.showMessage({'error': 'Nie udało się zmienić nazwy kategorii'});
			});
	})
	.on('move_node.jstree', function (e, data) {
		CATEGORYCONF.hideMessage();
		var params = {'id': data.node.id, 'parentId': data.parent, 'oldParentId': data.old_parent, 'order': data.position, 'oldOrder': data.old_position};
		$.post(request.baseUrl + '/cmsAdmin/category/move', params)
			.done(function (d) {
				if (d.status) {
					$('#jstree').jstree('deselect_all');
					$('#jstree').jstree('select_node', d.id);
				} else {
					data.instance.refresh();
				}
				CATEGORYCONF.showMessage(d);
			})
			.fail(function () {
				data.instance.refresh();
				CATEGORYCONF.showMessage({'error': 'Nie udało się przenieść kategorii'});
			});
	})
	.on('changed.jstree', function (e, data) {
		if (!data || !data.selected || !data.selected.length || !(0 in data.selected)) {
			return;
		}
		if (request.id == data.selected) {
			return;
		}
		$('#categoryContentContainer').empty();
	});
});

CATEGORYCONF.editForm = function (node) {
	window.location = request.baseUrl + '/cmsAdmin/category/index?id=' + node.id;
};

CATEGORYCONF.showMessage = function (data) {
	$('#categoryMessageContainer').empty();
	var msg = null;
	var msgClass = ' class="notice warning"';
	var icon = '<i class="icon-warning-sign icon-large"></i>';
	if (typeof data.error === 'string') {
		msg = data.error;
		msgClass = ' class="notice error"';
		icon = '<i class="icon-remove-sign icon-large"></i>';
	} else if (typeof data.message === 'string') {
		msg = data.message;
		msgClass = ' class="notice success"';
		icon = '<i class="icon-ok icon-large"></i>';
	}
	if (msg === null) {
		return;
	}
	var html = '<ul id="messenger">';
	html += '<li' + msgClass + '>' + icon + '<div class="alert">' + msg + '<a class="close-alert" href="#"></a></div></li>';
	html += '</ul>';
	$('#categoryMessageContainer').html(html);
	$('#categoryMessageContainer ul#messenger').show();
};

CATEGORYCONF.hideMessage = function () {
	$('#categoryMessageContainer').empty();
};