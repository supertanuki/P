var cookieinfo
function CookieExist(CookieName) {
	cookieinfo = document.cookie;
	if (cookieinfo.indexOf(CookieName) == -1) {
		return false;
	}
	else {
		return true;
	}
}

function SetCookie (name, value) {
        var argv=SetCookie.arguments;
        var argc=SetCookie.arguments.length;
        var expires=(argc > 2) ? argv[2] : null;
        var path=(argc > 3) ? argv[3] : null;
        var domain=(argc > 4) ? argv[4] : null;
        var secure=(argc > 5) ? argv[5] : false;
        document.cookie=name+"="+escape(value)+
                ((expires==null) ? "" : ("; expires="+expires.toGMTString()))+
                ((path==null) ? "" : ("; path="+path))+
                ((domain==null) ? "" : ("; domain="+domain))+
                ((secure==true) ? "; secure" : "");
}

function GetCookie(CookieName) {
	if ( CookieExist(CookieName) ){
		d = cookieinfo.indexOf(CookieName) + CookieName.length + 1;
		f = cookieinfo.indexOf(";",d);
		if ( f == -1) {
			f = cookieinfo.length;
		}
		return (cookieinfo.substring(d,f));
	} else {
		return false;
	}
}

if (GetCookie("sexy")!='1') 
{
	okoupas=confirm("Avertissement:\nVous entrez sur une page sexy du blog de PRONO+, page réservée aux adultes.\nElle est strictement interdit aux moins de 18 ans et elle est déconseillée aux personnes sensibles. Cliquez sur le bouton Annuler pour ne pas afficher cette page.");
	
	var pathname=location.pathname;
	var myDomain=pathname.substring(0,pathname.lastIndexOf('/')) +'/';
	var date_exp = new Date();
	date_exp.setTime(date_exp.getTime()+(2*24*3600*1000));

	if (okoupas==false)
	{
		SetCookie("sexy",'',date_exp);
		document.location = "/blog";
	} else {
		SetCookie("sexy",'1',date_exp);
	}
}