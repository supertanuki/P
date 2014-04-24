function selectSetValue(idselect, selectThis)
{
	sel = $(idselect);
	for (i=0; i<sel.options.length; i++) {
		if (sel.options[i].value == selectThis) {
			sel.selectedIndex = i;
		}
	}
}

function txt2send(text)
{
	text=''+text;
	return encodeURIComponent(text);
}

function txt2js(texte)
{
	texte = texte+'';
	texte = texte.replace(/"/g, "&quot;");
	texte = texte.replace(/'/g, "\'");
	texte = texte.replace(/</g, "&lt;");
	texte = texte.replace(/>/g, "&gt;");
	return texte;
}

function addslashes(texte)
{
	texte = texte+'';
	texte = texte.replace(/"/g, "&quot;");
	texte = texte.replace(/'/g, "\\\'");
	return texte;
}

function trim(str)
{
    return str.replace(/^\s*|\s*$/g,"");
}

function OpenOrCloseResultats(Open)
{
	if(Open == true)
	{
		if($('plus_de_resultats')) $('plus_de_resultats').hide();
		if($('moins_de_resultats')) $('moins_de_resultats').show();
		if($('resultats_historique')) $('resultats_historique').show();
		for(var i=4; i<=10; i++)
		{
			if($('resultats_matches_' + i)) $('resultats_matches_' + i).show();
		}
		
	} else {
		if($('plus_de_resultats')) $('plus_de_resultats').show();
		if($('moins_de_resultats')) $('moins_de_resultats').hide();
		if($('resultats_historique')) $('resultats_historique').hide();
		for(var i=4; i<=10; i++)
		{
			if($('resultats_matches_' + i)) $('resultats_matches_' + i).hide();
		}
	}
	return false;
}

function OpenOrCloseMatches(Type, Open, APartirDe)
{
	if(Open == true)
	{
		if($('plus_de_' + Type)) $('plus_de_' + Type).hide();
		if($('moins_de_' + Type)) $('moins_de_' + Type).show();
		for(var i=APartirDe+1; i<=100; i++)
		{
			if($('current_' + Type + '_' + i))
				$('current_' + Type + '_' + i).show();
			else
				break;
		}
		
	} else {
		if($('plus_de_' + Type)) $('plus_de_' + Type).show();
		if($('moins_de_' + Type)) $('moins_de_' + Type).hide();
		for(var i=APartirDe+1; i<=100; i++)
		{
			if($('current_' + Type + '_' + i))
				$('current_' + Type + '_' + i).hide();
			else
				break
		}
	}
	return false;
}

function OpenOrCloseInfo()
{
	Effect.toggle('header_window', 'slide', {duration:0.5});
}

function SeConnecter(html, msg, endfunction)
{	
	//if($('header_window').style.display == 'none') OpenOrCloseInfo();
	
	var html = '<div class="popup"><h2 class="title_grey">Se connecter</h2><br /><table width="100%"><tr><td valign="top" width="50%"><form id="form_se_connecter" onsubmit="login(\''+endfunction+'\'); return false;" style="margin:0"><table>';
	if(msg!='undefined' && msg!=null && msg!='') html += '<tr><td colspan="2"><strong>'+ msg +'</strong></td></tr>';
	html += '<tr><td>Pseudo</td><td><input type="text" name="connect_login" id="connect_login" class="inputText" maxlength="200" /></td></tr><tr><td>Mot de passe</td><td><input type="password" name="connect_password" id="connect_password" class="inputText" maxlength="200" /></td></tr><tr><td>Rester connect&eacute;</td><td><input type="checkbox" name="connect_permanent" id="connect_permanent" checked="checked" /></td></tr><tr><td></td><td><div id="connect_status" style="display:none"></div><div id="connect_btn">&nbsp;<br /><input type="submit" class="link_button" value="Valider" />&nbsp;&nbsp;&nbsp;&nbsp;ou <a href="#" onclick="OpenOrCloseInfo(); return false;" class="link_orange" />Fermer ?</a><br />&nbsp;</div></td></tr></table></form></td><td width="50%" valign="top"><a href="javascript:" class="link_orange" onclick="lostId(this, \''+txt2js(msg)+'\', \''+endfunction+'\');">Oops... j\'ai oubli&eacute; mes identifiants</a><br /><br /><br /><br />Je n\'ai pas d\'identifiants :&nbsp;&nbsp;<a href="javascript:"; onclick="Sinscrire(this, \''+txt2js(msg)+'\', \''+endfunction+'\');" class="link_button_urgent">S\'inscrire !</a></td></tr></table></div>';
	
	$('header_window').update(html);
	if($('header_window').style.display == 'none') OpenOrCloseInfo();
	setTimeout("LoginFocus()", 200);	
}

function LoginFocus()
{
	$('connect_login').focus();
}

function login(endfunction)
{
	$('connect_login').disabled = true;
	$('connect_password').disabled = true;	
	$('connect_permanent').disabled = true;
	$('connect_btn').style.display = 'none';
	$('connect_status').style.display = 'block';
	$('connect_status').update('<img src="/template/default/wait.gif" align="absmiddle" alt="" /> Vérification');
	
	var param = 'action=connect&login=' + ($('connect_login').value) + '&pwd=' + ($('connect_password').value) + '&permanent=' + ($('connect_permanent').checked);
	
	new Ajax.Request('/login.php', {
		method: 'post',
		parameters: param,
		onComplete: function(data) {
			var retour = eval('(' + data.responseText + ')');
			if (retour.response == 200)
			{
				//alert(endfunction);
				if(endfunction=='undefined' || endfunction==null)
					location.href='index.php';
				else
					setTimeout(endfunction, 1);
				
			} else {
				$('connect_status').update('<strong>Identification incorrecte !</strong>');
				$('connect_login').disabled = false;
				$('connect_password').disabled = false;	
				$('connect_permanent').disabled = false;
				$('connect_btn').style.display = 'block';
				$('connect_login').select();
				new Effect.Highlight('connect_status', {startcolor:'#ffff00', duration:1});
			}
		}
	});
}









function Sinscrire(html, msg, endfunction)
{	
	//if($('header_window').style.display == 'none') OpenOrCloseInfo();
	
	var html = '<div class="popup"><h2 class="title_green">S\'inscrire</h2><form id="form_sinscrire" onsubmit="SinscrireSubmit(\''+endfunction+'\'); return false;" style="margin:0"><br />';
	html += '<table width="100%">';
	if(msg!='undefined' && msg!=null && msg!='') html += '<tr><td colspan="2"><strong>'+ msg +'</strong></td></tr>';
	html += '<tr><td valign="top">Attention :</td>';
	html += '<td>L\'inscription à PRONO+ vous sera facturé 0,00€ soit Gratuit. L\'équipe de Prono+ décline toute responsabilité en cas d\'addiction névrosée au jeu.</td></tr>';
	html += '<tr><td>Pseudo</td>';
	html += '<td><input type="text" maxlength="100" name="login" id="signin_login" value="" class="inputText"> (au moins 4 caract&egrave;res)</td></tr>';
	html += '<tr><td>Mot de passe</td>';
	html += '<td><input type="password" maxlength="100" id="signin_password" class="inputText"> (au moins 8 caract&egrave;res)</td></tr>';
	html += '<tr><td>Email</td>';
	html += '<td><input type="text" maxlength="150" size="40" id="signin_email" name="email" value="" class="inputText"></td></tr>';
	html += '<tr><td nowrap="nowrap">Fuseau horaire</td>';
	html += '<td><select id="signin_fuseau" class="inputText">';
	html += '<option value="-12">(GMT -12h) Eniwetok, Kwajalein</option>';
	html += '<option value="-11">(GMT -11h) Iles Midway, Samoa</option>';
	html += '<option value="-10">(GMT -10h) hawaii</option>';
	html += '<option value="-9">(GMT -9h) Alaska</option>';
	html += '<option value="-8">(GMT -8h) Pacifique (USA &amp; Canada), Tijuana</option>';
	html += '<option value="-7">(GMT -7h) Montagnes (USA &amp; Canada), Arizona</option>';
	html += '<option value="-6">(GMT -6h) Central (USA &amp; Canada), Mexico City</option>';
	html += '<option value="-5">(GMT -5h) Est (USA &amp; Canada), Bogota, Lima, Quito</option>';
	html += '<option value="-4">(GMT -4h) heure Atlantique (Canada), Caracas, Antilles</option>';
	html += '<option value="-3.5">(GMT -3:30h) Terre-Neuve</option>';
	html += '<option value="-3">(GMT -3h) Brasilia, Buenos Aires, Georgetown, Falkland Is</option>';
	html += '<option value="-2">(GMT -2h) Centre-Atlantique, Ascension Is., St. helena</option>';
	html += '<option value="-1">(GMT -1h) Les Açores, Iles du Cap Vert</option>';
	html += '<option value="0">(GMT) Casablanca, Dublin, Edinburgh, Londres, Lisbonne, Monrovia</option>';
	html += '<option value="1" selected="selected">(GMT +1h) Amsterdam, Berlin, Bruxelles, Madrid, Paris, Rome</option>';
	html += '<option value="2">(GMT +2h) Le Caire, helsinki, Kaliningrad, Afrique du Sud</option>';
	html += '<option value="3">(GMT +3h) Bagdad, Riyah, Moscow, Nairobi</option>';
	html += '<option value="3.5">(GMT +3:30h) Téhéran</option>';
	html += '<option value="4">(GMT +4h) Abu Dhabi, Baku, Muscat, Tbilisi</option>';
	html += '<option value="4.5">(GMT +4:30h) Kaboul</option>';
	html += '<option value="5">(GMT +5h) Ekaterinburg, Islamabad, Karachi, Tashkent</option>';
	html += '<option value="5.5">(GMT +5:30h) Bombay, Calcutta, Madras, New Delhi</option>';
	html += '<option value="6">(GMT +6h) Almaty, Colombo, Dhaka, Novosibirsk</option>';
	html += '<option value="6.5">(GMT +6:30h) Rangoon</option>';
	html += '<option value="7">(GMT +7h) Bangkok, hanoï, Djakarta</option>';
	html += '<option value="8">(GMT +8h) Pékin, hong Kong, Perth, Singapour, Taïpei</option>';
	html += '<option value="9">(GMT +9h) Osaka, Sapporo, Seoul, Tokyo, Yakutsk</option>';
	html += '<option value="9.5">(GMT +9:30h) Adélaïde, Darwin</option>';
	html += '<option value="10">(GMT +10h) Canberra, Guam, Melbourne, Sydney, Vladivostok</option>';
	html += '<option value="11">(GMT +11h) Magadan, New Caledonia, Solomon Islands</option>';
	html += '<option value="12">(GMT +12h) Auckland, Wellington, Fiji, Marshall Island</option>';
	html += '</select></td></tr>';
	html += '<tr><td valign="top">Question-piège</div></td>';
	html += '<td>Combien fait la somme de <b>2 + 2</b> ?<br /><input type="text" maxlength="100" id="signin_captcha" class="inputText"></td>';
	html += '</tr>';
	html += '<tr><td></td><td><div id="signin_status" style="display:none"></div><div id="signin_btn">&nbsp;<br /><input type="submit" value="Valider" class="link_button" />&nbsp;&nbsp;&nbsp;&nbsp;ou <a href="#" onclick="OpenOrCloseInfo(); return false;" class="link_orange" />Fermer ?</a><br />&nbsp;</div></td></tr>';
	html += '</table></form></div>';
	
	$('header_window').update(html);
	if($('header_window').style.display == 'none') OpenOrCloseInfo();
}


function SinscrireSubmit(endfunction)
{
	$('signin_login').disabled = true;
	$('signin_password').disabled = true;	
	$('signin_email').disabled = true;
	$('signin_captcha').disabled = true;
	$('signin_fuseau').disabled = true;
	$('signin_btn').style.display = 'none';
	$('signin_status').style.display = 'block';
	$('signin_status').update('<img src="/template/default/wait.gif" align="absmiddle" alt="" /> Vérification');
	
	var param = 'action=signin&login=' + ($('signin_login').value) + '&pwd=' + ($('signin_password').value) + '&email=' + ($('signin_email').value) + '&captcha=' + ($('signin_captcha').value) + '&fuseau=' + ($('signin_fuseau')[$('signin_fuseau').selectedIndex].value);
	
	new Ajax.Request('/signin.php', {
		method: 'post',
		parameters: param,
		onComplete: function(data) {
			var retour = eval('(' + data.responseText + ')');
			if(retour.response == 200)
			{
				if(endfunction=='undefined' || endfunction==null)
					location.href='index.php';
				else
					setTimeout(endfunction, 1);
					
			} else {
				//alert('400 '+data.responseText);
				$('signin_status').update('<strong>'+retour.msg+'</strong>');
				$('signin_login').disabled = false;
				$('signin_password').disabled = false;	
				$('signin_email').disabled = false;
				$('signin_captcha').disabled = false;
				$('signin_fuseau').disabled = false;
				$('signin_btn').style.display = 'block';
				new Effect.Highlight('signin_status', {startcolor:'#ffff00', duration:1});
			}
		}
	});
}



function lostId(html, msg, endfunction)
{	
	//if($('header_window').style.display == 'none') OpenOrCloseInfo();
	
	var html = '<div class="popup"><h2 class="title_blue">Identifiants perdus</h2><form id="form_lostId" onsubmit="lostIdSubmit(\''+txt2js(msg)+'\', \''+endfunction+'\'); return false;" style="margin:0"><br />';
	html += '<table width="100%">';
	html += '<tr><td colspan="2">Pour recevoir un rappel de vos identifiants, veuillez saisir votre email d\'inscription.</td></tr>';
	html += '<tr><td>Email</td>';
	html += '<td><input type="text" maxlength="150" size="40" id="lostId_email" name="email" value="" class="inputText"></td></tr>';
	html += '<tr><td></td><td><div id="lostId_status" style="display:none"></div><div id="lostId_btn">&nbsp;<br /><input type="submit" value="Valider" class="link_button" />&nbsp;&nbsp;&nbsp;&nbsp;ou <a href="#" onclick="OpenOrCloseInfo(); return false;" class="link_orange" />Fermer ?</a<br />&nbsp;</div></td></tr>';
	html += '</table></form></div>';
	
	$('header_window').update(html);
	//OpenOrCloseInfo();
	if($('header_window').style.display == 'none') OpenOrCloseInfo();
}

function lostIdSubmit(msg, endfunction)
{
	$('lostId_email').disabled = true;
	$('lostId_btn').style.display = 'none';
	$('lostId_status').style.display = 'block';
	$('lostId_status').update('<img src="/template/default/wait.gif" align="absmiddle" alt="" /> Vérification');
	
	var param = 'action=lostid&email=' + ($('lostId_email').value);
	
	new Ajax.Request('/login.php', {
		method: 'post',
		parameters: param,
		onComplete: function(data) {
			var retour = eval('(' + data.responseText + ')');
			if (retour.response == 200)
			{
				$('lostId_status').update('<strong>L\'email a été envoyé</strong><br /><br /><input type="button" onclick="SeConnecter($(\'login_link\'), \''+txt2js(msg)+'\', \''+endfunction+'\');" value="Se connecter" class="link_button" />');
				return;
				
			} else {
				$('lostId_status').update('<strong>Votre email n\'a pas été trouvé !</strong>');
				$('lostId_email').disabled = false;
				$('lostId_btn').style.display = 'block';
				$('lostId_email').select();
				new Effect.Highlight('lostId_status', {startcolor:'#ffff00', duration:1});
			}
		}
	});
}


function ScrollTo(content)
{
	new Effect.ScrollTo(content, {
							offset: Position.realOffset($(content))[1] - document.documentElement.scrollTop,
							duration:0.5
						});
}


var oembed_providers = new Array(
	new Array('#http://(www\.)?youtube.com/watch.*#i', 	'http://www.youtube.com/oembed'),
	new Array('http://blip.tv/file/*', 					'http://blip.tv/oembed/'),
	new Array('#http://(www\.)?vimeo\.com/.*#i',		'http://www.vimeo.com/api/oembed.{format}'),
	new Array('#http://(www\.)?dailymotion\.com/.*#i',	'http://www.dailymotion.com/api/oembed'),
	new Array('#http://(www\.)?flickr\.com/.*#i',		'http://www.flickr.com/services/oembed/'),
	new Array('#http://(www\.)?hulu\.com/watch/.*#i',	'http://www.hulu.com/api/oembed.{format}'),
	new Array('#http://(www\.)?viddler\.com/.*#i',		'http://lab.viddler.com/services/oembed/'),
	new Array('http://qik.com/*',						'http://qik.com/api/oembed.{format}'),
	new Array('http://revision3.com/*',					'http://revision3.com/api/oembed/'),
	new Array('http://i*.photobucket.com/albums/*',		'http://photobucket.com/oembed'),
	new Array('http://gi*.photobucket.com/groups/*',	'http://photobucket.com/oembed'),
	new Array('#http://(www\.)?scribd\.com/.*#i',		'http://www.scribd.com/services/oembed'),
	new Array('http://wordpress.tv/*',					'http://wordpress.tv/oembed/')
);

/*
function formate_texte_oembed(obj)
{
	//'?url='+url+'&format=json';
	var url = 'http://oohembed.com/oohembed/?url=' + encodeURIComponent('http://www.dailymotion.com/video/xcmzqk_le-piege-de-lyabstention_news');
	
	new Ajax.Request(url, {
		method: 'get',
		onSuccess: function(transport)
		{
			alert(transport.headerJSON);
			var json = transport.responseText.evalJSON(true);
			alert(json.html);
		}
	});
}

formate_texte_oembed('');
*/

/* smileys */
/*
function smiley(sm, comment_field) {
	var area = $(comment_field);
	if (!area) return;
	var pre, pre, selStart,selEnd;
	area.focus();
	
	alert('area = '+area);
	alert('value = '+area.value);

	if (document.selection && !window.opera) {
		// I REALLY HATE Internet Explorer!!!!! me too !
		var sel = document.selection.createRange();
		var sel2 = sel.duplicate();
		sel2.moveToElementText(area);
		sel2.setEndPoint('StartToEnd', sel);
		selEnd = area.value.length-sel2.text.length;
		sel2.setEndPoint('StartToStart', sel);
		selStart = area.value.length-sel.text.length;
		pre = area.value.substring(0,selStart);
		
		sm = spaceSmiley(sm,pre);
		sel.text = sm;
	}
	else {
		selStart = area.selectionStart;
		selEnd = area.selectionEnd;
		var testo = area.value;
		pre = testo.substring(0,selStart);
		post = testo.substring(selEnd);
		// if the character before the smiley is not a space, let's
		// add one before it. And add a space to the end.
		sm = spaceSmiley(sm, pre);
		
		var risultato = pre+sm+post;
		area.value = risultato;
	}
	
	area.selectionEnd = area.selectionStart = selStart + sm.length;
}

function spaceSmiley(sm,pre) {
	// Add one ending space, always.
	sm = sm + ' ';

	var lastPreSmileyChar = pre.substr(pre.length - 1, 1);
	if (lastPreSmileyChar && lastPreSmileyChar != ' '
		&& lastPreSmileyChar.charCodeAt(0) != 10
		&& lastPreSmileyChar.charCodeAt(0) != 13) {
		sm = ' '+sm;
	}
	return sm;
}
*/
/* smileys */



function smiley(text, fld)
{
		if(!fld) var fld = document.envoyer_msg.message;
        text = ' ' + text + ' ';
        if (fld.createTextRange && fld.message.caretPos)
		{
			var caretPos = fld.caretPos;
			caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
			fld.focus();
        } else {
        	fld.value  += text;
        	fld.focus();
        }
}

function storeCaret(textEl) {
        if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}


function envoyer_message() {
	if(document.envoyer_msg.titre.value=="") {
		alert("Mettre un titre !");
		return false;
	} else if(document.envoyer_msg.message.value=="") {
		alert("Mettre un message !");
		return false;
	} else {
		return true;
	}
}

function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function P7_Snap() { //v2.62 by PVII
  var x,y,ox,bx,oy,p,tx,a,b,k,d,da,e,el,args=P7_Snap.arguments;a=parseInt(a);
  for (k=0; k<(args.length-3); k+=4)
   if ((g=MM_findObj(args[k]))!=null) {
    el=eval(MM_findObj(args[k+1]));
    a=parseInt(args[k+2]);b=parseInt(args[k+3]);
    x=0;y=0;ox=0;oy=0;p="";tx=1;da="document.all['"+args[k]+"']";
    if(document.getElementById) {
     d="document.getElementsByName('"+args[k]+"')[0]";
     if(!eval(d)) {d="document.getElementById('"+args[k]+"')";if(!eval(d)) {d=da;}}
    }else if(document.all) {d=da;} 
    if (document.all || document.getElementById) {
     while (tx==1) {p+=".offsetParent";
      if(eval(d+p)) {x+=parseInt(eval(d+p+".offsetLeft"));y+=parseInt(eval(d+p+".offsetTop"));
      }else{tx=0;}}
     ox=parseInt(g.offsetLeft);oy=parseInt(g.offsetTop);var tw=x+ox+y+oy;
     if(tw==0 || (navigator.appVersion.indexOf("MSIE 4")>-1 && navigator.appVersion.indexOf("Mac")>-1)) {
      ox=0;oy=0;if(g.style.left){x=parseInt(g.style.left);y=parseInt(g.style.top);
      }else{var w1=parseInt(el.style.width);bx=(a<0)?-5-w1:-10;
      a=(Math.abs(a)<1000)?0:a;b=(Math.abs(b)<1000)?0:b;
      x=document.body.scrollLeft + event.clientX + bx;
      y=document.body.scrollTop + event.clientY;}}
   }else if (document.layers) {x=g.x;y=g.y;var q0=document.layers,dd="";
    for(var s=0;s<q0.length;s++) {dd='document.'+q0[s].name;
     if(eval(dd+'.document.'+args[k])) {x+=eval(dd+'.left');y+=eval(dd+'.top');break;}}}
   if(el) {e=(document.layers)?el:el.style;
   var xx=parseInt(x+ox+a),yy=parseInt(y+oy+b);
   if(navigator.appName=="Netscape" && parseInt(navigator.appVersion)>4){xx+="px";yy+="px";}
   if(navigator.appVersion.indexOf("MSIE 5")>-1 && navigator.appVersion.indexOf("Mac")>-1){
    xx+=parseInt(document.body.leftMargin);yy+=parseInt(document.body.topMargin);
    xx+="px";yy+="px";}e.left=xx;e.top=yy;}}
}

function MM_showHideLayers() { //v6.0
  var i,p,v,obj,args=MM_showHideLayers.arguments;
  for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
    if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
    obj.visibility=v; }
}