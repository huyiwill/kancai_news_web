$(function () {
		function P (arg) {
			console.log(arg);
		}
		/**
		 * 排行
		 */
		(function () {
			var hotlist = $('.hotlist');
			if (hotlist.size() == 0) return ;
			var tab = hotlist.find("div.hd ul li");
			var itemUl = hotlist.find("div.bd ul");
			itemUl.hide();
			var currNum = 0;
			tab.each(function (i) {
				(function (i) {
					tab.eq(i).mouseenter(function () {
						P(i);
						active(i);
					});
				})(i)
			});
			active(0);
			function active (No) {
				tab.eq(currNum).find("a").css({
					color:'#8B8B8B'
				});
				tab.eq(No).find("a").css({
					color:'#00AA98'
				});
				itemUl.eq(currNum).hide();
				itemUl.eq(No).show();
				currNum = No;
			} 
		})();
});