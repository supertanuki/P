/*--------------------------------------------------------------*/
// HTML TABLE SORTER - OBJECT ORIENTED JAVASCRIPT IMPLEMENTATION OF QUICKSORT
// @author	Terrill Dent 
// @source	http://www.terrill.ca
// @date	August 28th, 2006
/*--------------------------------------------------------------*/
function TSorter(){
	var table = Object;
	var trs = Array;
	var ths = Array;
	var curSortCol = Object;
	var prevSortCol = '1';
	var sortType = Object;

	function get(){}

	function getCell(index){
		return trs[index].cells[curSortCol] 
	}

	this.init = function(tableName, initSortCol)
	{
		if(initSortCol) prevSortCol=initSortCol;
		table = document.getElementById(tableName);
		ths = table.getElementsByTagName("th");
		for(var i = 0; i < ths.length ; i++)
		{
			ths[i].onclick = function()
			{
				sort(this);
			}
		}
		return true;
	};
	
	function sort(oTH)
	{
		curSortCol = oTH.cellIndex;
		sortType = oTH.abbr;
		trs = table.tBodies[0].getElementsByTagName("tr");

		setGet(sortType)

		for(var j=0; j<trs.length; j++)
		{
			if(trs[j].className == 'detail_row')
			{
				closeDetails(j+2);
			}
		}

		if(prevSortCol == curSortCol)
		{
			oTH.className = (oTH.className != 'ascend' ? 'ascend' : 'descend' );
			reverseTable();
		}
		else
		{
			oTH.className = 'ascend';
			if(ths[prevSortCol].className != 'exc_cell'){ths[prevSortCol].className = '';}
			quicksort(0, trs.length);
		}
		prevSortCol = curSortCol;
	}
	
	function setGet(sortType)
	{
		switch(sortType)
		{   
			case "image_number":
				get = function(index){	
					return getCell(index).childNodes[1].nodeValue;
				}
				break;
			case "link":
				get = function(index){
					return  getCell(index).firstChild.firstChild.nodeValue;
				};
				break;
			case "input_text":
				get = function(index){	
					return getCell(index).firstChild.value;
				};
				break;
			case "number":
				get = function(index){	
					return parseInt(getCell(index).firstChild.nodeValue, 10);
				}
				break;
			case "float":
				get = function(index){	
					return 1 * getCell(index).firstChild.nodeValue;
				}
				break;
			default:
				get = function(index){	return getCell(index).firstChild.nodeValue;};
				break;
		};	
	}

	function exchange(i, j)
	{
		if(i == j+1) {
			table.tBodies[0].insertBefore(trs[i], trs[j]);
		} else if(j == i+1) {
			table.tBodies[0].insertBefore(trs[j], trs[i]);
		} else {
			var tmpNode = table.tBodies[0].replaceChild(trs[i], trs[j]);
			if(typeof(trs[i]) == "undefined") {
				table.appendChild(tmpNode);
			} else {
				table.tBodies[0].insertBefore(tmpNode, trs[i]);
			}
		}
	}
	
	function reverseTable()
	{
		for(var i = 1; i<trs.length; i++)
		{
			table.tBodies[0].insertBefore(trs[i], trs[0]);
		}
	}

	function quicksort(lo, hi)
	{
		if(hi <= lo+1) return;
		 
		if((hi - lo) == 2) {
			if(get(hi-1) > get(lo)) exchange(hi-1, lo);
			return;
		}
		
		var i = lo + 1;
		var j = hi - 1;
		
		if(get(lo) > get(i)) exchange(i, lo);
		if(get(j) > get(lo)) exchange(lo, j);
		if(get(lo) > get(i)) exchange(i, lo);
		
		var pivot = get(lo);
		
		while(true) {
			j--;
			while(pivot > get(j)) j--;
			i++;
			while(get(i) > pivot) i++;
			if(j <= i) break;
			exchange(i, j);
		}
		exchange(lo, j);
		
		if((j-lo) < (hi-j)) {
			quicksort(lo, j);
			quicksort(j+1, hi);
		} else {
			quicksort(j+1, hi);
			quicksort(lo, j);
		}
	}
}
