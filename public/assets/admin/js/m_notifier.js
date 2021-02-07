const ANIM_DELAY = 3000
let alive =false;

function display(message, bgcolor="#FAFAFA")
{
    if (!alive){
        document.querySelector("body").innerHTML += `
        <div class="notification" id="notif"></div>
        `;
    }
    let notif = document.querySelector("#notif");
    notif.innerHTML = `<h4>${message}</h4>`
    notif.style.animation = ANIM_DELAY + "ms appear";
    notif.style.backgroundColor = bgcolor;
    if (!alive) 
    {
        setTimeout(()=>{
            console.log("delete.");
            document.querySelector("#notif")?.remove();
            alive = false;
        },ANIM_DELAY);
        alive =true;
    }
}

function positive(message)
{
    display(message, "#c9ffcc")
}

function negative(message)
{
    display(message, "#ffc9c9")
}

function info(message)
{
    display(message, "#ffffc9")
}