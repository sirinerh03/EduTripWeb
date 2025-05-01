# Système de Récompenses Aléatoires pour les Avis

Ce document explique comment le système de récompenses aléatoires (bundle spin) a été mis en place pour les utilisateurs qui donnent leur avis sur EduTrip.

## Fonctionnalités

- Les utilisateurs qui laissent un avis peuvent gagner une réduction aléatoire (10%, 20% ou 30%)
- Une animation de roue de la fortune est affichée pour révéler la réduction gagnée
- Les réductions sont stockées dans le compte de l'utilisateur et peuvent être réclamées
- Interface utilisateur intuitive avec des effets visuels attractifs

## Structure du système

### Entités

1. **SpinReward** : Stocke les différentes réductions possibles
   - `id` : Identifiant unique
   - `percentage` : Pourcentage de réduction (10%, 20%, 30%)
   - `description` : Description de la récompense
   - `isActive` : Indique si la récompense est active

2. **Avis** (modifié) : Ajout de relations avec SpinReward
   - `spinReward` : Relation ManyToOne avec SpinReward
   - `rewardClaimed` : Indique si la récompense a été réclamée

### Services

- **SpinRewardService** : Gère l'attribution aléatoire des réductions
  - `assignRandomReward()` : Attribue une récompense aléatoire à un avis
  - `claimReward()` : Marque une récompense comme réclamée

### Contrôleurs

- **SpinRewardController** : Gère les actions liées aux récompenses
  - `spin()` : Affiche l'animation de la roue et la récompense gagnée
  - `claim()` : Permet à l'utilisateur de réclamer sa récompense

- **AvisController** (modifié) : Intègre le système de récompenses
  - Attribue automatiquement une récompense lors de la création d'un avis
  - Redirige vers la page de spin après la création d'un avis avec récompense

### Templates

- **spin_reward/spin.html.twig** : Affiche l'animation de la roue et la récompense
- **avis/show.html.twig** (modifié) : Affiche les informations sur la récompense obtenue

## Étapes d'installation

1. Créer l'entité SpinReward et son repository
2. Modifier l'entité Avis pour ajouter les relations avec SpinReward
3. Créer le service SpinRewardService
4. Créer le contrôleur SpinRewardController
5. Modifier le contrôleur AvisController
6. Créer les templates nécessaires
7. Exécuter les migrations pour mettre à jour la base de données
8. Initialiser les récompenses avec la commande `app:create-spin-rewards`

## Utilisation

1. L'utilisateur crée un nouvel avis
2. Une récompense aléatoire lui est attribuée
3. L'utilisateur est redirigé vers la page de spin qui affiche l'animation
4. L'utilisateur peut réclamer sa récompense
5. La récompense est marquée comme réclamée et peut être utilisée pour un voyage éducatif

## Commandes utiles

- `php bin/console app:create-spin-rewards` : Crée les récompenses de base (10%, 20%, 30%)
- `php bin/console doctrine:migrations:migrate` : Exécute les migrations pour mettre à jour la base de données

## Personnalisation

Pour modifier les pourcentages de réduction disponibles, modifiez la commande `CreateSpinRewardsCommand.php` et ajoutez ou modifiez les récompenses dans le tableau `$rewards`.
