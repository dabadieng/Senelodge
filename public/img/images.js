//Attendre que le DOM soit chargé
window.onload = () => {
    //Gestion des liens supprimer grace à l'attribut data-delete
    let links = document.querySelectorAll("[data-delete]")

    //boucler sur la selection 
    for (link of links) {
        //On récupère le click 
        link.addEventListener("click", function(e) {
            //Désactiver le lien pour empêcher la navigation
            e.preventDefault()

            //Demander la confirmation avant de supprimer 
            if (confirm("Voulez-vous supprimer cette image ?")) {
                // On envoie une requête ajax vers le href du lien avec la méthode DELETE
                fetch(this.getAttribute("href"), {
                    method: "DELETE",
                    headers: {
                        "X-Requested-with": "XMLHttpRequest",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ "_token": this.dataset.token })
                }).then(
                    //On récupère la réponse en JSON cela est une promesse 
                    response => response.json()
                ).then(data => {
                    //On vérifie si data du controleur contien success
                    //si oui il faut supprimer le parent dans notre cas la div 
                    if (data.success)
                        this.parentElement.remove()
                    else
                        alert(data.error)
                }).catch(e => alert(e))
            }
        })
    }


}