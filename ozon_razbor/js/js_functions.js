function alerting(){
    var lock_1 = document.getElementById('up_input'); 
      if (lock_1) {
          lock_1.className = 'LockOn'; 
          // lock.innerHTML = str; 
      }
      var lock_2 = document.getElementById('down_input'); 
      if (lock_2) {
          lock_2.className = 'LockOn'; 
          // lock.innerHTML = str; 
      }

      var see_text = document.getElementById('OnLock_textLockPane'); 
      if (see_text) {
          see_text.className = 'LockOff'; 
          // lock.innerHTML = str; 
      }



  }