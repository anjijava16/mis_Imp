checked = false;
var loanidvalues = new Array();
var returnvalue = '';
function AdvanceView(isPrint, printtype) {

	$.ajax({
		type : "GET",
		url : "../jsp/advanceView.htm",
		data :   "&isPrint=" + isPrint + "&printtype=" + printtype,
		success : function(response) {
			
			$('#content').html(response);
		}
	/* error: function(e){  
	  alert('Error: ' + e);  
	}  */
	});
}

function testhello()
{
window.alert("hello world I'm in testhello function");	
}

function AdvanceType(objectid,isPrint,printtype) {

	$.ajax({
		type : "GET",
		url : "../jsp/advanceType.htm",
		data : "&objectid=" + objectid + "&isPrint=" + isPrint + "&printtype=" + printtype ,
		success : function(response) {
			/* window.alert("hello"); */
			
			$('#content').html(response);
		}
	/* error: function(e){  
	  alert('Error: ' + e);  
	}  */
	});
}

function PartWithdrawlReport() {

	$.ajax({
		type : "POST",
		url : "../jsp/partWithdrawlReport.htm",

		success : function(response) {
			/* window.alert("hello"); */
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function PartWithdrawlView(isPrint, printtype) {

	$.ajax({
		type : "GET",
		url : "../jsp/partWithdrawlView.htm",
		data : "&isPrint=" + isPrint + "&printtype=" + printtype,
		success : function(response) {
			/* window.alert("hello"); */
			$('#content').html(response);
		}
	/* error: function(e){  
	  alert('Error: ' + e);  
	}  */
	});
}

function AdvanceReport() {

	$.ajax({
		type : "POST",
		url : "../jsp/advanceReport.htm",

		success : function(response) {
			/* window.alert("hello"); */
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function xmlView(isPrint, printtype) {

	$.ajax({
		type : "GET",
		url : "../jsp/xmlView.htm",
		data : "&isPrint=" + isPrint + "&printtype=" + printtype,
		success : function(response) {
			/* window.alert("hello"); */
			$('#content').html(response);
		}
	/* error: function(e){  
	  alert('Error: ' + e);  
	}  */
	});
}

function xmlReport() {

	$.ajax({
		type : "POST",
		url : "../jsp/xmlReport.htm",

		success : function(response) {
			/* window.alert("hello"); */
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function GazettedView(isPrint, printtype) {

	$.ajax({
		type : "GET",
		url : "../jsp/gazettedView.htm",
		data : "&isPrint=" + isPrint + "&printtype=" + printtype,
		success : function(response) {

			$('#content').html(response);
		}

	});
}

function GazettedReport() {

	$.ajax({
		type : "POST",
		url : "../jsp/gazettedReport.htm",

		success : function(response) {
			/* window.alert("hello"); */
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function NonGazettedView(isPrint, printtype) {

	$.ajax({
		type : "GET",
		url : "../jsp/nonGazettedView.htm",
		data : "&isPrint=" + isPrint + "&printtype=" + printtype,
		success : function(response) {
			/* window.alert("hello"); */
			$('#content').html(response);
		}
	/* error: function(e){  
	  alert('Error: ' + e);  
	}  */
	});
}

function NonGazettedReport() {

	$.ajax({
		type : "POST",
		url : "../jsp/nonGazettedReport.htm",

		success : function(response) {
			/* window.alert("hello"); */
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function XmlExpView(isPrint, printtype) {
	/* alert("I am here"); */
	$.ajax({
		type : "GET",
		url : "../jsp/xmlExpView.htm",
		data : "&isPrint=" + isPrint + "&printtype=" + printtype,
		success : function(response) {

			$('#content').html(response);
		}
	/* error: function(e){  
	  alert('Error: ' + e);  
	}  */
	});
}

function XmlExpReport() {

	$.ajax({
		type : "POST",
		url : "../jsp/xmlExpReport.htm",

		success : function(response) {
			/* window.alert("hello"); */
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function WithdrawlView(isPrint, printtype) {

	$.ajax({
		type : "GET",
		url : "../jsp/withdrawlView.htm",

		data : "&isPrint=" + isPrint + "&printtype=" + printtype,

		success : function(response) {
			/* window.alert("hello"); */
			$('#content').html(response);
		}
	/* error: function(e){  
	  alert('Error: ' + e);  
	}  */
	});
}

function WithdrawlReport() {

	$.ajax({
		type : "POST",
		url : "../jsp/withdrawlReport.htm",

		success : function(response) {
			/*  window.alert("hello");  */
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function BalanceView(isPrint, printtype) {

	$.ajax({
		type : "GET",
		url : "../jsp/balanceView.htm",
		data : "&isPrint=" + isPrint + "&printtype=" + printtype,
		success : function(response) {
			/* window.alert("hello"); */
			$('#content').html(response);
		}
	/* error: function(e){  
	  alert('Error: ' + e);  
	}  */
	});
}

function BalanceReport() {

	$.ajax({
		type : "POST",
		url : "../jsp/balanceReport.htm",

		success : function(response) {
			/* window.alert("hello"); */
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function SubscriberView(isPrint, printtype) {

	$.ajax({
		type : "GET",
		url : "../jsp/subscriberView.htm",
		data : "&isPrint=" + isPrint + "&printtype=" + printtype,
		success : function(response) {
			/* 	window.alert("hello"); */
			$('#content').html(response);
		}

	});
}

function SubscriberReport() {

	$.ajax({
		type : "POST",
		url : "../jsp/subscriberReport.htm",

		success : function(response) {
			/*  window.alert("hello");  */
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function gpfPayment() {

	$.ajax({
		type : "POST",
		url : "../jsp/sanctionslist.htm",
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function checkedAll() {
	if (checked == false) {
		checked = true
	} else {
		checked = false
	}

	for ( var i = 0; i < document.getElementById('loanform').elements.length; i++) {
		//loanidvalues[i]=document.getElementById('loanform').elements[i].value;
		document.getElementById('loanform').elements[i].checked = checked;
	}

}

function gpfPaymentList() {
	var returnvalue='';
	var valuecount=0;
	for ( var i = 0; i < document.getElementById('loanform').elements.length; i++) {

		if(document.getElementById('loanform').elements[i].checked==true && document.getElementById('loanform').elements[i].value!='on'){
			
			returnvalue=returnvalue+','+document.getElementById('loanform').elements[i].value;
			valuecount++;
		}
	}
	returnvalue = returnvalue.substr(1);
	
	$.ajax({
		type : "POST",
		url : "../jsp/gpfloanrelease.htm",
		data: "loanId=" + returnvalue,

		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function uploadmonthstmt() {

	$.ajax({
		type : "POST",
		url : "../jsp/uploadmonthstmt.htm",
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}
function gpfapplication() {

	$.ajax({
		type : "GET",
		url : "../jsp/gpfapplication.htm",
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function gpfloanreleasestmt() {

	$.ajax({
		type : "GET",
		url : "../jsp/gpf-loan-release-stmt.htm",
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function accountinfo() {

	$.ajax({
		type : "POST",
		url : "../jsp/accountinfo.htm",
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function gpfslips() {

	$.ajax({
		type : "POST",
		url : "../jsp/gpfslips.htm",
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}
function payunitschedule() {

	$.ajax({
		type : "POST",
		url : "../jsp/payunitschedule.htm",
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function pendingapplications() {

	$.ajax({
		type : "POST",
		url : "../jsp/pendingapplications.htm",
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function sanctionapplications() {
	var gpfacc = $('#gpfacc').val();
	var finyear = $('#finyear').val();
	$.ajax({
		type : "POST",
		url : "../jsp/pendingapplication.htm",
		data : "gpfacc=" + gpfacc + "&finyear=" + finyear,
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function center() {

	$.ajax({
		type : "GET",
		url : "../jsp/center.htm",
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function Upload() {

	$.ajax({
		type : "POST",
		url : "../jsp/uploadmonthstmt.htm",
		success : function(response) {
			$('#content').html(response);
		}
	/* error: function(e){  
	  alert('Error: ' + e);  
	}  */
	});
}

function gpfLnRel() {

	$.ajax({
		type : "GET",
		url : "../jsp/gpfloanrelease.htm",
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function gpfTrans() {

	$.ajax({
		type : "POST",
		url : "../jsp/gpfloanrelease.htm",
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function Pendappln() {

	$.ajax({
		type : "POST",
		url : "../jsp/gpfsanction.htm",
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function Sanctionappln(objectId) {

	$.ajax({
		type : "POST",
		url : "../jsp/gpfsanction.htm",
		data : "objectId=" + objectId,
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function Sanction() {

	$.ajax({
		type : "POST",
		url : "../jsp/gpfsanc.htm",
		success : function(response) {
			$('#content').html(response);
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}
function LoanRelstmt() {

	$.ajax({
		type : "GET",
		url : "../jsp/gpf-loan-release-stmt.htm",
		success : function(response) {
			$('#content').html(response);
		}
	/* error: function(e){  
	  alert('Error: ' + e);  
	}  */
	});
}

function centerForm() {
	// get the form values  
	var objectId = $('#objectId').val();
	var employeeName = $('#employeeName').val();
	var employeeId = $('#employeeId').val();
	var dateOfBirth = $('#dateOfBirth').val();
	var dateOfjoin = $('#dateOfjoin').val();
	var nominee = $('#nominee').val();
	var designation = $('#designation').val();
	var workingLocation = $('#workingLocation').val();
	var gPFACNo = $('#gPFACNo').val();
	var openingBalanceRs = $('#openingBalanceRs').val();
	var openingBalanceAsOnDate = $('#openingBalanceAsOnDate').val();
	
	alert(employeeName);

	//alert("my   value-->"+gPFACNo); 
	$.ajax({
		type : "POST",
		url : "../jsp/centerForm.htm",
		data : "&employeeName=" + employeeName + "&employeeId=" + employeeId
				+ "&dateOfBirth=" + dateOfBirth + "&dateOfjoin=" + dateOfjoin
				+ "&nominee=" + nominee + "&designation=" + designation
				+ "&workingLocation=" + workingLocation + "&gPFACNo=" + gPFACNo
				+ "&openingBalanceRs=" + openingBalanceRs
				+ "&openingBalanceAsOnDate=" + openingBalanceAsOnDate
				+ "&objectId=" + objectId,

		success : function(response) {
			// we have the response  
			$('#content').html(response);
			$('#gPFACNo').val('');
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function recordform() {
	// get the form values  
	var payunit = $('#payUnit').val();
	var month = $('#queryMonth').val();
	var year = $('#queryYear').val();
	//alert("my   dddfgvalue-->"+year); 
	$.ajax({
		type : "POST",
		url : "../jsp/recordform.htm",
		data : "payUnit=" + payunit + "&queryMonth=" + month + "&queryYear="
				+ year,

		success : function(response) {
			// we have the response  
			$('#content').html(response);
			$('#payunit').val('');
			$('#month').val('');
			$('#year').val('');
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function payunitform() {
	// get the form values  
	var payunit = $('#payunit').val();
	var month = $('#month').val();
	var year = $('#year').val();

	//alert("my   value-->"+year); 
	$.ajax({
		type : "POST",
		url : "../jsp/payunitform.htm",
		data : "payunit=" + payunit + "month=" + month + "&year=" + year,

		success : function(response) {
			// we have the response  
			$('#info').html(response);
			$('#payunit').val('');
			$('#month').val('');
			$('#year').val('');
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function gpfpendngapp() {
	// get the form values  
	var gpfAcc = $('#gpfAcc').val();
	var finYear = $('#finYear').val();

	//alert("my   value-->"+finYear); 
	$.ajax({
		type : "POST",
		url : "../jsp/gpfpendngapp.htm",
		data : "gpfAcc=" + gpfAcc + "&finYear=" + finYear,

		success : function(response) {
			// we have the response  
			$('#info').html(response);
			$('#gpfAcc').val('');
			$('#finYear').val('');
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function pendngapp() {
	// get the form values  
	var gpfAcc = $('#gpfAcc').val();
	var finYear = $('#finYear').val();

	//alert("my   value-->"+gpfAcc); 
	$.ajax({
		type : "POST",
		url : "../jsp/pendngapp.htm",
		data : "gpfAcc=" + gpfAcc + "&finYear=" + finYear,

		success : function(response) {
			// we have the response  
			$('#info').html(response);
			$('#gpfAcc').val('');
			$('#finYear').val('');
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function sanapp() {
	// get the form values  
	var gpfAcc = $('#gpfAcc').val();
	var finYear = $('#finYear').val();

	//alert("my   value-->"+finYear); 
	$.ajax({
		type : "POST",
		url : "../jsp/sanapp.htm",
		data : "gpfAcc=" + gpfAcc + "&finYear=" + finYear,

		success : function(response) {
			// we have the response  
			$('#info').html(response);
			$('#gpfAcc').val('');
			$('#finYear').val('');
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function gpfSanction() {
	// get the form values  
	var loanids = $('#loanid').val();
	var relAmt = $('#relamt').val();
	var chkNo = $('#chequeno').val();
	var cdate = $('#chcquedate').val();

	//alert("my   value-->"+loanids); 
	$.ajax({
		type : "POST",
		url : "../jsp/gpfloanrelease.htm",
		data : "relamt=" + relAmt + "&chequeno=" + chkNo + "&chcquedate=" + cdate+ "&loanId=" + loanids,

		success : function(response) {
			
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function gpftransform() {
	// get the form values  
	var gpfAcc = $('#gpfAcc').val();
	var year = $('#year').val();
	var month = $('#month').val();

	//alert("my   value-->"+gpfAcc); 
	$.ajax({
		type : "POST",
		url : "../jsp/gpftransform.htm",
		data : "gpfAcc=" + gpfAcc + "year=" + year + "&month=" + month,

		success : function(response) {
			// we have the response  
			$('#info').html(response);
			$('#gpfAcc').val('');
			$('#year').val('');
			$('#month').val('');
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function sandat() {
	// get the form values  
	var sanAmt = $('#sanAmt').val();
	var sanRef = $('#sanRef').val();
	var sanDt = $('#sanDt').val();

	//alert("my   value-->"+sanAmt); 
	$.ajax({
		type : "POST",
		url : "../jsp/sandat.htm",
		data : "sanAmt=" + sanAmt + "sanRef=" + sanRef + "&sanDt=" + sanDt,

		success : function(response) {
			// we have the response  
			$('#info').html(response);
			$('#sanAmt').val('');
			$('#sanRef').val('');
			$('#sanDt').val('');
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function sandata() {
	// get the form values

	var objid = $('#objectID').val();
	var sanAmt = $('#sanctionAmount').val();
	var sanRef = $('#sanctionRef').val();
	var sanDt = $('#sanctionDate').val();
	var noinst = $('#noOfInstallments').val();
	var dstmonth = $('#deductStartMonth').val();

	//alert("my   value-->" + sanAmt);
	$.ajax({
		type : "POST",
		url : "../jsp/sandata.htm",
		data : "sanctionAmount=" + sanAmt + "&sanctionRef=" + sanRef
				+ "&sanctionDate=" + sanDt + "&objectID=" + objid
				+ "&noOfInstallments=" + noinst + "&deductStartMonth="
				+ dstmonth,

		success : function(response) {
			// we have the response  
			$('#content').html(response);

		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function gpfslipform() {
	// get the form values  
	var payUnit = $('#payUnit').val();
	var finYear = $('#finYear').val();

	//alert("my   value-->"+payunit); 
	$.ajax({
		type : "POST",
		url : "../jsp/gpfslipform.htm",
		data : "payUnit=" + payUnit + "finYear=" + finYear,

		success : function(response) {
			// we have the response  
			$('#info').html(response);
			$('#payUnit').val('');
			$('#finYear').val('');
			$('#month').val('');
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

function particulars() {
	// get the form values  
	var gpfnumber = $('#gpfnumber').val();
	var appdt = $('#appdt').val();
	var amount = $('#amount').val();
	var loantyp = $('#loantyp').val();

	var banknam = $('#banknam').val();
	var bankbranch = $('#bankbranch').val();
	var bankAcNo = $('#bankAcNo').val();

	var ifsCode = $('#ifsCode').val();
	var purpose = $('#purpose').val();

	//alert("my   value-->"+loantyp); 
	$.ajax({
		type : "POST",
		url : "../jsp/particulars.htm",
		data : "&gpfnumber=" + gpfnumber + "&appdt=" + appdt + "&amount="
				+ amount + "&loantyp=" + loantyp + "&banknam=" + banknam
				+ "&bankbranch=" + bankbranch + "&bankAcNo=" + bankAcNo
				+ "&ifsCode=" + ifsCode + "&purpose=" + purpose,

		success : function(response) {
			// we have the response  
			$('#content').html(response);

		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}



/*----------------------home------------*/
function  homecall()
{
	alert("home");
	$.ajax({
		type : "GET",
		url : "../jsp/home.htm",
		success : function(response) {
			$('#content').html(response);
			//alert("tttt");
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}



/*----------------------order------------*/
function  ordercall()
{
	alert("order");
	$.ajax({
		type : "GET",
		url : "../jsp/order.htm",
		success : function(response) {
			$('#content').html(response);
			//alert("tttt");
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}


/*----------------------editorder------------*/
function  editordercall()
{
	alert("edit order");
	$.ajax({
		type : "GET",
		url : "../jsp/editorder.htm",
		success : function(response) {
			$('#content').html(response);
			//alert("tttt");
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

/*----------------------stock------------*/
function  stockcall()
{
	alert("stock");
	$.ajax({
		type : "GET",
		url : "../jsp/stock.htm",
		success : function(response) {
			$('#content').html(response);
			//alert("tttt");
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

/*----------------------issue------------*/
function  issuecall()
{
	alert("issue");
	$.ajax({
		type : "GET",
		url : "../jsp/issue.htm",
		success : function(response) {
			$('#content').html(response);
			//alert("tttt");
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

/*----------------------reports------------*/
function  reportscall()
{
	alert("reports");
	$.ajax({
		type : "GET",
		url : "../jsp/reports.htm",
		success : function(response) {
			$('#content').html(response);
			//alert("tttt");
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}


/*----------------------contactus------------*/
function  contactuscall()
{
	alert("contactus");
	$.ajax({
		type : "GET",
		url : "../jsp/contactus.htm",
		success : function(response) {
			$('#content').html(response);
			//alert("tttt");
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}



/*----------------------search------------*/
function searchcall()
{
	alert("search");
	$.ajax({
		type : "GET",
		url : "../jsp/search.htm",
		success : function(response) {
			$('#content').html(response);
			//alert("tttt");
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}










function saveordercall() {
	// get the form values  
	//alert("TEST-1");
	var objectId = $('#objectId').val();
	
	var purchaseOrderId = $('#purchaseOrderId').val();
	var purchaseOrderSubject = $('#purchaseOrderSubject').val();
	var purchaseOrderOffice = $('#purchaseOrderOffice').val();
	var delivaryAt = $('#delivaryAt').val();
	var delivaryDate = $('#delivaryDate').val();
	var companyName = $('#companyName').val();
	var companyAddress = $('#companyAddress').val();
	var companyContact = $('#companyContact').val();
	
	//alert(unitCode+" : "+unitName+" : "+unitAddress);

	//alert("my   value-->"+gPFACNo); 
	$.ajax({
		type : "POST",
		url : "../jsp/saveorder.htm",
		data : "&purchaseOrderId=" + purchaseOrderId + "&purchaseOrderSubject=" +purchaseOrderSubject+ "&purchaseOrderOffice=" + purchaseOrderOffice+ "&delivaryAt=" + delivaryAt+ "&delivaryDate=" + delivaryDate+ "&companyName=" + companyName+ "&companyAddress=" + companyAddress+ "&companyContact=" + companyContact+ "&objectId=" + objectId,
		success : function(response) {
			// we have the response  
			$('#content').html(response);			
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}



function saveitemordercall() {
	// get the form values  
	//alert("TEST-1");
	var objectId = $('#objectId').val();
	
	var item = $('#item').val();
	var quantity = $('#quantity').val();
	var discription = $('#discription').val();
	
	//alert(unitCode+" : "+unitName+" : "+unitAddress);

	//alert("my   value-->"+gPFACNo); 
	$.ajax({
		type : "POST",
		url : "../jsp/saveitemorder.htm",
		data : "&item=" + item + "&quantity=" +quantity+ "&description=" + description+ "&objectId=" + objectId,
		success : function(response) {
			// we have the response  
			$('#content').html(response);			
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

















function  gpfsectiondisplay()
{
	//alert("tttt");
	$.ajax({
		type : "GET",
		url : "../jsp/payunitform.htm",
		success : function(response) {
			$('#content').html(response);
			//alert("tttt");
		},
		error : function(e) {
			alert('Error: ' + e);
		}
	});
}

