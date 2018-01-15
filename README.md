# Xee Application pour Eedomus
Suivez votre véhicule à l'aide de [Xee](http://www.xee.com/) et son API à partir de votre box Eedomus.

Script créé par [@Thibautg16](https://twitter.com/Thibautg16/)

## Prérequis 
Vous devez au préalable disposer d'un boitié Xee installé et configuré sur le site ou l'application Xee.

## Installation
### Ajout du périphérique 
Cliquez sur "Configuration" / "Ajouter ou supprimer un prériphérique" / "Store eedomus" / "Xee" / "Créer"

![xee_eedomus_creer](https://user-images.githubusercontent.com/4451322/34133139-6a700086-e453-11e7-8ab4-9df79aebdba4.png)

### Configuration périphérique
Vous devez renseigner les différents champs:

* [Optionnel] - Nom personnalisé : personnalisation du nom de votre périphérique
* [Obligatoire] - Pièce : vous devez définir dans qu'elle pièce se trouve votre caméra
* [Obligatoire] - Identifiant véhicule


Périphériques non visibles créés obligatoirement :

* [Obligatoire] - Position : position de votre véhicule 

Puis cocher/décocher les périphériques afin de personnaliser suivant vos souhaits les périphériques créés : 

* [Optionnel] - Vitesse : vitesse de votre véhicule (Km/h)
* [Optionnel] - Vitesse Moteur : vitesse moteur de votre véhicule (Tr/min)
* [Optionnel] - Kilomètrage : kilomètrage de votre véhicule (Km)
* [Optionnel] - Batterie : tension de votre véhicule (V)
* [Optionnel] - Carburant : carburant restant dans votre véhicule (L)
* [Optionnel] - Phares : etat des phares de votre véhicule (Allumés/Eteint)
* [Optionnel] - Codes : etat des codes de votre véhicule (Allumés/Eteint)
* [Optionnel] - Veilleuses : etat des veilleuses de votre véhicule (Allumés/Eteint)
* [Optionnel] - Verrouillage : verouillage de votre véhicule (Ouvert/Fermé)
* [Optionnel] - Etat : etat de votre véhicule (On/Off)



Plusieurs modules sont créés sur votre box eedomus, suivant les canaux choisis:

![xee_eedomus_widget](https://user-images.githubusercontent.com/4451322/34132405-f3e8f100-e44f-11e7-998c-49bb461ea43b.png)


## Mise à jour script
Si vous possédez déjà le périphérique et que vous souhaitez simplement profiter de la mise à jour du script.
Dans un premier temps vous rendre dans la configuration de votre périphérique et cliquer sur "Vérifier les mises à jour de xee_oauth.php":

![xee_eedomus_script_verif](https://user-images.githubusercontent.com/4451322/34959888-bda63d2e-fa38-11e7-93ca-5022effda527.png)


Cliquez alors sur "Mettre à jour xee_oauth.php avec la dernière version disponible.":

![xee_eedomus_script_maj](https://user-images.githubusercontent.com/4451322/34960084-af7cbb3c-fa39-11e7-8ff1-b31f13cb525d.png)



![Release](https://img.shields.io/github/release/Thibautg16/XeeAppEedomus.svg?style=for-the-badge)
![Licence : GNU GPL v3.0](https://img.shields.io/github/license/Thibautg16/XeeAppEedomus.svg?style=for-the-badge)
![Status : Attente Validation](https://img.shields.io/badge/Status-Attente_Validation-red.svg?style=for-the-badge)
![Twitter : @Thibautg16](https://img.shields.io/badge/twitter-@Thibautg16-blue.svg?style=for-the-badge)
