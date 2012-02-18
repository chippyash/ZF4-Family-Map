
/** Change the data table that is being edited **/
var _lastSel;
function dmSel(ele,table) {
    if (_lastSel != 'undefined') {
        $(_lastSel).removeClass('dmSel');
    }
    $(ele).addClass('dmSel');
    _lastSel = ele;
    window.location='/input/' + table;
}

var editPopupWidth = 500;
var _gridOptions = {
    left: (window.innerWidth/2)-(editPopupWidth/2),
    width: editPopupWidth,
    top : 200,
    height: 'auto',
    dataheight: 'auto',
    modal: false,
    drag: false,
    resize: false,
    mtype : "POST",
    clearAfterAdd : true,
    closeAfterEdit : true,
    closeOnEscape:true,
    reloadAfterSubmit : true,
    recreateForm : false,
    jqModal : true,
    addedrow : "first",
    topinfo : '',
    savekey: [false,13],
    navkeys: [false,38,40],
    viewPagerButtons : true,
    addCaption:"Add System User",
    editCaption:"Edit System User",
    afterSubmit: function(response,postdata) {
        var json=response.responseText;
        var result=eval("("+json+")");
        if (result.success) {
            return [true,'',result.data.id];
        } else {
            return [false,result.msg,postdata.id];
        }
    },
    errorTextFormat: function(response) {
        var msg = response.responseText;
        return msg.msg;
    },
    url:'/input/user?format=json'
}
var _addOptions = $.extend({},_gridOptions,{
    editData:{
        'oper':'add'
    }
});
var _editOptions = $.extend({},_gridOptions,{
    editData:{
        'oper':'edit'
    }
});

var _delOptions = $.extend({},_gridOptions,{
    delData:{
        'oper':'del'
    }
});

//onLoad
$(document).ready(function(){
    //turn on table selector
    _lastSel = '#dmUser';
    $(_lastSel).addClass('dmSel');
    var staff = {};

    $.ajax({
        url:'/input/sel',
        data:{
            'format':'json',
            'sel':'staff'
        },
        async:false,
        success:function(response) {
            if (response.success) {
                staff = {'-1':'Not Linked'};
                for (x in response.data) staff[x] = response.data[x];
            } else {
                dlgError(response.msg);
            }
        },
        dataType:'json'
    });

    jQuery("#dataList").jqGrid({
        url:'/input/user?format=json',
        datatype: "json",
        colNames:['Id','OrgId','User Name','Email','Payroll Id','Status','Last Logon','Role','Linked Staff Name'],
        colModel:[
        {
            name:'id',
            index:'id',
            width:10,
            editable:false,
            hidden:true
        },

        {
            name:'orgId',
            index:'orgId',
            width:10,
            editable:false,
            hidden:true
        },

        {
            name:'uName',
            index:'uName',
            width:100,
            sortable:true,
            editable:true,
            editrules:{
                required:true
            }
        },

        {
        name:'uEmail',
        index:'uEmail',
        width:200,
        editable:true,
        editrules:{
            required:true
        }
    },

    {
        name:'payrollId',
        index:'payrollId',
        width:70,
        editable:true,
        editrules:{
            required:true
        }
    },
{
    name:'rowSts',
    index:'rowSts',
    width:80,
    editable:true,
    edittype:"select",
    editoptions:{
        value:{
            'active':'Active',
            'suspended':'Suspended'
        }
    },
editrules:{
    required:true
}
},
{
    name:'lastLogon',
    index:'lastLogon',
    width:120,
    editable:false
},
{
    name:'role',
    index:'role',
    width:60,
    editable:true,
    edittype:"select",
    editoptions:{
        value:{
            2:'Admin',
            3:'Inputter',
            4:'User'
        }
    },
editrules:{
    required:true
}
},
{
    name:'prsnId',
    index:'prsnId',
    width:60,
    hidden:true,
    editable:true,
    edittype:"select",
    editoptions:{
        value:staff
    },
    editrules:{
        required:false,
        edithidden:true
    }
}
],
rowNum:30,
rowList:[10,20,30,40,50],
pager: '#dataListPager',
sortname: 'uName',
viewrecords: true,
sortorder: "desc",
caption:"System User Maintenance",
autowidth:true,
height:350,
scroll:false,
hidegrid:false,
shrinkToFit:false
});

jQuery("#dataList").jqGrid('navGrid','#dataListPager',
{
    edit:true,
    add:true,
    del:true
},
_editOptions,
_addOptions,
_delOptions
);
//set additional actions form attributes
$('#dmDownload').attr('action','/input/user/format/csv');	
	
	
});
