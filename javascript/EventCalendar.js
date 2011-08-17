Behaviour.register({
	'#EventCalendarHolder table td.event_link a' : {
		onblur : function(){
			var block = this.findHashLinkedBlock();
			if(block){
				new Effect.Highlight(block, {startcolor: '#9FBFEB', endcolor: '#ffffff',  duration:3});
			}
		},
		findHashLinkedBlock: function(){
			if(this.href.match(/\/#([\d]{4}-[\d]{2}-[\d]{2})$/)){
				var blockid = RegExp.$1;
				return $(blockid);
			}
		}
	}
});