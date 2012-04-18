/**
 * Make list view items sortable
 * @param object
 */

window.addEvent('domready', function ()
{

	var container = $$('table.tl_listing tbody')[0];

	// just return if theres no container (ie "no elements")
	if(!container) return;

	// also return if theres only one element
	if(container.getElements('td:first-child').length <= 1) return;

	container.getElements('td:first-child').setStyle('cursor', 'move');

	var list = new Sortables($$('table.tl_listing tbody'), {
		contstrain:true,
		handle:'td:first-child',
		opacity:0.6
	});

	list.active = false;

	list.addEvent('start', function ()
	{
		list.active = true;
	});

	list.addEvent('complete', function (el)
	{
		if (!list.active)
		{
			return;
		}

		if (el.getPrevious('tr'))
		{
			var tmp = el.getElement('a').get('href');
			tmp = tmp.match(/id=(\d+)/);
			var id = tmp[1];

			var tmp = el.getPrevious('tr').getElement('a').get('href');
			tmp = tmp.match(/id=(\d+)/);
			var afterid = tmp[1];

			var strDo = window.location.search.match(/do=([^&]+)/);
			strDo = strDo[1];
			if(window.location.search.match(/id=\d+/))
			{
				var req = window.location.search.replace(/id=[0-9]*/, 'id=' + id);
			}
			else
			{
				var req = window.location.search + (window.location.search.length > 0 ? '&' : '?') + 'id='+id;
			}
			req += '&action=listViewSortable&afterid=' + afterid + '&REQUEST_TOKEN='+REQUEST_TOKEN;
			new Request({
				url:		window.location.href.replace(/\?.*$/, '')+'?do='+strDo,
				method:		'post'
			}).send(req);
		}
		else if (el.getParent('table'))
		{
			alert('Sorting to the beginning is not supported yet, sorry :-(');
			document.location.reload();
		}
	});
});