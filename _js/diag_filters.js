 jQuery(document).ready(function(){
    /*список ѕЅ— при выборе √–Ѕс*/
			$('.id_grbs1').click(function(){
			    var dann ='';
				$( '.id_grbs1:checkbox:checked' ).each(function(){
                 //alert("s");
		             dann = dann + ',' +this.value;
		    });
              //  alert(dann);
				$.post("/ajax/show_org.php", {id:dann}, function(data){
					$('#org_list').html(data);
         	$('#org_list2').html(data);
				}, 'html');

				//return false;
			});
     
      $(function() {    
				var dd = new DropDown( $('#grbs_list') );
        var dd1 = new DropDown( $('#org_list') );
        var dd3 = new DropDown( $('#grbs_list2') );
        var dd4 = new DropDown( $('#org_list2') );
        var dd5 = new DropDown( $('#grbs_list3') );
        var dd6 = new DropDown( $('#org_list3') );
				$(document).click(function() {
					// all dropdowns
					$('.wrapper-dropdown-4').removeClass('active');
				});
		});
       /*конец список ѕЅ— при выборе √–Ѕс*/
    }); 