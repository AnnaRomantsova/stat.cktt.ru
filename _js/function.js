
function submit_forms(formid)
  {

       var form = document.getElementById(formid);
       form.submit();

  }

function submit_dalee(formid)
  {
     var isChecked = $(":radio[name=ans]").filter(":checked").val();
     if (isChecked ) {
       var form = document.getElementById(formid);
       form.submit();
     };
  }

function auto_submit_dalee(formid)
  {
       var form = document.getElementById(formid);
       form.submit();

  }


  function dalee()
  {
     var but = document.getElementById('dalee');
     var isChecked = $(":radio[name=ans]").filter(":checked").val();

     if (isChecked ) but.className = 'btn-yes';
       else but.className = 'btn-no';
  }

  function go_register()
  {
     var lic = document.getElementById('lic_ch');
     var fl = lic.checked;

     if (fl == true) {submit_forms('reg');};
      //else  lic.style.display='none';
  }


   function license1(obj)
  {
     var lic = document.getElementById('lic1');
     var but = document.getElementById('regbut1');
     if (obj.checked ) {
             lic.style.display='block';
             but.className = 'btn-yes';

      } else  {
            lic.style.display='none';
            but.className = 'btn-no';
      };
  }

  function go_register1()
  {
     var lic = document.getElementById('lic1');

     if (lic.style.display=='block') submit_forms('reg1');
      //else  lic.style.display='none';
  }