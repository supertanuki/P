/* This tooltip library was created by Mukul Kumar; http://codeeazy.com */
function showtip(e,message){var x=0;var y=0;var m;var h;if(!e)
var e=window.event;if(e.pageX||e.pageY){x=e.pageX;y=e.pageY;}
else if(e.clientX||e.clientY){x=e.clientX+document.body.scrollLeft+document.documentElement.scrollLeft;y=e.clientY+document.body.scrollTop+document.documentElement.scrollTop;}
m=document.getElementById('mktipmsg');if((y>10)&&(y<450)){m.style.top=y-4+"px";}
else{m.style.top=y+4+"px";}
var messageHeigth=(message.length/20)*10+25;if((e.clientY+messageHeigth)>510)
{m.style.top=y-messageHeigth+"px";}
if(x<850){m.style.left=x+20+"px";}
else{m.style.left=x-170+"px";}
m.innerHTML=message;m.style.display="block";m.style.zIndex=203;}
function hidetip(){var m;m=document.getElementById('mktipmsg');m.style.display="none";}
