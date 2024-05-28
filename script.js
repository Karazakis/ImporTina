
function showPopup() {
	// Mostra il pop-up
	document.getElementById('popup').style.display = 'none';
  }
function showSecondPopup() {
    // Mostra il pop-up
    document.getElementById('softclean').style.display = 'none';
    } 
function showSoftClean()
{
  document.getElementById('softclean').style.display = 'block';
}
function showHardClean()
{
  document.getElementById('hardclean').style.display = 'block';
}
function showTrash()
{
  document.getElementById('trashclean').style.display = 'block';
}
  function accept() {
	// Codice da eseguire quando viene premuto il pulsante di accettazione
  }
  
  function cancel() {
	document.getElementById('popup').style.display = 'none';
  document.getElementById('softclean').style.display = 'none';
  document.getElementById('hardclean').style.display = 'none';
  document.getElementById('trashclean').style.display = 'none';
  }
  
  function showbtn()
  {
	document.getElementById('popup').style.display = 'block';
  
  }
