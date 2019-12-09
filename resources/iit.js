function validate(formObj) {
  
  if (formObj.item.value === '') {
    alert('Please enter an item!');
    formObj.item.focus();
    return false;
  }
  
  if (formObj.description.value === '') {
    alert('Please enter a description');
    formObj.description.focus();
    return false;
  }
    
  return true;
}


jQuery(document).ready(function($) {
  // $(".clicktr").click(function() {
  //     // window.location = '/';
  // });
  $(".cactus").click(function(t) {
    //alert(t.target.parentNode.id);
    var res = prompt("Has this item been found? Type YES to confirm.");
    if (res == "YES") {
      var parent = t.target.parentNode.id;
      parent = parent.substr(parent.indexOf('-') + 1);
      var postData = 'id=' + parent;
      $.ajax({
        type: 'post',
        url: 'exec.php',
        dataType: 'json',
        data: postData,
        success: function(responseData, status){
          
          if (responseData.errors) {
            alert(responseData.errno + ' ' + responseData.error);
          } else {
            // Uncomment the following line to see the repsonse message from the server
            alert(responseData.message);
            location.reload();
          }
        },
        error: function(msg) {
          // there was a problem
          alert(msg.status + ' ' + msg.statusText);
        }
      });
    }
  });
});