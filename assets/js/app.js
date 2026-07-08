// SIDEBAR

const toggle=document.querySelector(".toggle-sidebar");

if(toggle){

toggle.addEventListener("click",()=>{

document.querySelector(".sidebar").classList.toggle("hide");

});

}