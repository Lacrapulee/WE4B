import { Routes } from '@angular/router';
import { CatalogueComponent } from './features/catalogue/catalogue.component';
import { ItemDetailComponent } from './features/item-detail/item-detail.component';
import { PageComponent } from './features/page/page.component';
import { PostComponent } from './features/post/post.component';
import { ProfileComponent } from './features/profile/profile';
import { MessagesComponent } from './features/messages/messages';
import { FavorisComponent } from './features/favoris/favoris';
import { CommandesComponent } from './features/commandes/commandes';
import { Connexion } from './features/connexion/connexion';
import { Inscription } from './features/inscription/inscription';
import { EditItemComponent } from './features/edit-item/edit-item.component';
import { PaiementComponent } from './features/paiement/paiement.component';

export const routes: Routes = [
  { path: '', pathMatch: 'full', redirectTo: 'catalogue' },
  { path: 'catalogue', component: CatalogueComponent },
  { path: 'item/:id', component: ItemDetailComponent },
  { path: 'edit-item/:id', component: EditItemComponent, data: { title: 'Modifier mon annonce' } },
  { path: 'vendre', component: PostComponent, data: { title: 'Vendre', description: 'Publiez une nouvelle annonce depuis Angular.' } },
  { path: 'favoris', component: FavorisComponent, data: { title: 'Favoris', description: 'Retrouvez ici les annonces enregistrées.' } },
  { path: 'mes-commandes', component: CommandesComponent, data: { title: 'Mes commandes', description: 'Suivi des commandes passées.' } },
  { path: 'messages', component: MessagesComponent, data: { title: 'Messages', description: 'Messagerie intégrée de l’application.' } },
  { path: 'paiement/:id', component: PaiementComponent, data: { title: 'Paiement', description: 'Paiement de l’article.' } },
  { path: 'login', component: Connexion, data: { title: 'Connexion', description: 'Page de connexion Angular.' } },
  { path: 'inscription', component: Inscription, data: { title: 'Inscription', description: 'Création de compte Angular.' } },
  { path: 'a-propos', component: PageComponent, data: { title: 'À propos', description: 'Présentation du projet et de son fonctionnement.' } },
  { path: 'aide', component: PageComponent, data: { title: 'Aide', description: 'Réponses aux questions courantes.' } },
  { path: 'cgu', component: PageComponent, data: { title: 'Conditions générales d\'utilisation', description: 'Cadre légal et conditions d’utilisation.' } },
  { path: 'user/:id', component: ProfileComponent, data: { title: 'Profil utilisateur', description: 'Tableau de bord du compte utilisateur.' } },
  { path: '**', redirectTo: 'catalogue' }
];