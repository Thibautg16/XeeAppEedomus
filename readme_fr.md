# Xee Application pour Eedomus
Suivez votre v�hicule � l'aide de [Xee](http://www.xee.com/) et son API � partir de votre box Eedomus.

Script cr�� par [@Thibautg16](https://twitter.com/Thibautg16/)

Mise � jour v5.0 par [@Fabmaurel](https://twitter.com/fabmaurel/)

## Pr�requis 
Vous devez au pr�alable disposer d'un boiti� Xee install� et configur�.

## Installation
### Ajout du p�riph�rique 
Cliquez sur "Configuration" / "Ajouter ou supprimer un pr�riph�rique" / "Store eedomus" / "Xee" / "Cr�er"

![xee_eedomus_creer](https://user-images.githubusercontent.com/4451322/34133139-6a700086-e453-11e7-8ab4-9df79aebdba4.png)

### Configuration p�riph�rique
Vous devez renseigner les diff�rents champs:

* [Optionnel] - Nom personnalis� : personnalisation du nom de votre p�riph�rique
* [Obligatoire] - Pi�ce : vous devez d�finir dans qu'elle pi�ce se trouve votre cam�ra
* [Obligatoire] - Identifiant v�hicule


P�riph�riques non visibles cr��s obligatoirement :

* [Obligatoire] - Position : position de votre v�hicule 

Puis cocher/d�cocher les p�riph�riques afin de personnaliser suivant vos souhaits les p�riph�riques cr��s : 

* [Optionnel] - Vitesse : vitesse de votre v�hicule (Km/h)
* [Optionnel] - Vitesse Moteur : vitesse moteur de votre v�hicule (Tr/min)
* [Optionnel] - Kilom�trage : kilom�trage de votre v�hicule (Km)
* [Optionnel] - Batterie : tension de votre v�hicule (V)
* [Optionnel] - Carburant : carburant restant dans votre v�hicule (L)
* [Optionnel] - Phares : etat des phares de votre v�hicule (Allum�s/Eteint)
* [Optionnel] - Codes : etat des codes de votre v�hicule (Allum�s/Eteint)
* [Optionnel] - Veilleuses : etat des veilleuses de votre v�hicule (Allum�s/Eteint)
* [Optionnel] - Verrouillage : verouillage de votre v�hicule (Ouvert/Ferm�)
* [Optionnel] - Etat : etat de votre v�hicule (On/Off)

Des p�riph�riques sp�cifiques aux v�hicules �lectriques sont aussi disponibles :

* [Optionnel] - VE - Tension batterie traction (V)
* [Optionnel] - VE - Niveau de charge batterie traction (%)
* [Optionnel] - VE - Autonomie estim�e (km)
* [Optionnel] - VE - Temps de charge restant (mn)
* [Optionnel] - VE - Etat de sant� de la batterie traction (%)


Plusieurs modules sont cr��s sur votre box eedomus, suivant les canaux choisis:

![xee_eedomus_widget](https://user-images.githubusercontent.com/4451322/34132405-f3e8f100-e44f-11e7-998c-49bb461ea43b.png)


## Mise � jour script
Si vous poss�dez d�j� le p�riph�rique et que vous souhaitez simplement profiter de la mise � jour du script.
Dans un premier temps vous rendre dans la configuration de votre p�riph�rique et cliquer sur "V�rifier les mises � jour de xee_oauth.php":
![xee_eedomus_script_verif](https://user-images.githubusercontent.com/4451322/34959888-bda63d2e-fa38-11e7-93ca-5022effda527.png)

Cliquez alors sur "Mettre � jour xee_oauth.php avec la derni�re version disponible.":
![xee_eedomus_script_maj](https://user-images.githubusercontent.com/4451322/34960084-af7cbb3c-fa39-11e7-8ff1-b31f13cb525d.png)


![Release](https://img.shields.io/github/release/Thibautg16/XeeAppEedomus.svg?style=for-the-badge)
![Licence : GNU GPL v3.0](https://img.shields.io/github/license/Thibautg16/XeeAppEedomus.svg?style=for-the-badge)
![status_prod](https://img.shields.io/badge/Status-Prod-green.svg?style=for-the-badge)

![Twitter : @Thibautg16](https://img.shields.io/badge/twitter-@Thibautg16-blue.svg?style=for-the-badge)
![Twitter : @FabMaurel](https://img.shields.io/badge/twitter-@Fabmaurel-blue.svg?style=for-the-badge)