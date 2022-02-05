var currentstep = 0; // Current step is set to be the first step (0)
showstep(currentstep); // Display the current step

function showstep(n) {
  // This function will display the specified step of the form ...
  var x = document.getElementsByClassName("step");
  x[n].style.display = "block";
  // ... and fix the Previous/Next buttons:
  if (n == 0) {
    document.getElementById("prevBtn").style.display = "none";
  } else {
    document.getElementById("prevBtn").style.display = "inline";
  }
  if (n == (x.length - 1)) {
    document.getElementById("nextBtn").innerHTML = "Submit";
  } else {
    document.getElementById("nextBtn").innerHTML = "Next";
  }
  // ... and run a function that displays the correct step indicator:
  fixStepIndicator(n)
}

function nextPrev(n) {
  // This function will figure out which step to display
  var x = document.getElementsByClassName("step");
  // Exit the function if any field in the current step is invalid:
  if (n == 1 && !validateForm()) return false;
  // Hide the current step:
  x[currentstep].style.display = "none";
  // Increase or decrease the current step by 1:
  currentstep = currentstep + n;
  // if you have reached the end of the form... :
  if (currentstep >= x.length) {
    //...the form gets submitted:
    document.getElementById("regForm").submit();
    return false;
  }
  // Otherwise, display the correct step:
  showstep(currentstep);
}

function validateForm() {
  // This function deals with validation of the form fields
  var x, y, i, valid = true;
  x = document.getElementsByClassName("step");
  y = x[currentstep].getElementsByTagName("input");
  // A loop that checks every input field in the current step:
  for (i = 0; i < y.length; i++) {
    // If a field is empty...
    if (y[i].value == "") {
      // add an "invalid" class to the field:
      y[i].className += " invalid";
      // and set the current valid status to false:
      valid = false;
    }
  }
  // If the valid status is true, mark the step as finished and valid:
  if (valid) {
    document.getElementsByClassName("istep")[currentstep].className += " finish";
  }
  return valid; // return the valid status
}

function fixStepIndicator(n) {
  // This function removes the "active" class of all steps...
  var i, x = document.getElementsByClassName("istep");
  for (i = 0; i < x.length; i++) {
    x[i].className = x[i].className.replace(" active", "");
  }
  //... and adds the "active" class to the current step:
  x[n].className += " active";
}