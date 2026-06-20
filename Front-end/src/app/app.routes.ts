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
import { AdminComponent } from './features/admin/admin.component';
import { authGuard } from './core/guards/auth.guard';
import { noAuthGuard } from './core/guards/no-auth.guard';
import { adminGuard } from './core/guards/admin.guard';

export const routes: Routes = [
  { path: '', pathMatch: 'full', redirectTo: 'catalogue' },
  { path: 'catalogue', component: CatalogueComponent },
  { path: 'item/:id', component: ItemDetailComponent },
  { path: 'edit-item/:id', component: EditItemComponent, canActivate: [authGuard], data: { title: 'Modifier mon annonce' } },
  { path: 'vendre', component: PostComponent, canActivate: [authGuard], data: { title: 'Vendre', description: 'Publiez une nouvelle annonce depuis Angular.' } },
  { path: 'favoris', component: FavorisComponent, canActivate: [authGuard], data: { title: 'Favoris', description: 'Retrouvez ici les annonces enregistrées.' } },
  { path: 'mes-commandes', component: CommandesComponent, canActivate: [authGuard], data: { title: 'Mes commandes', description: 'Suivi des commandes passées.' } },
  { path: 'messages', component: MessagesComponent, canActivate: [authGuard], data: { title: 'Messages', description: 'Messagerie intégrée de l’application.' } },
  { path: 'paiement/:id', component: PaiementComponent, canActivate: [authGuard], data: { title: 'Paiement', description: 'Paiement de l’article.' } },
  { path: 'login', component: Connexion, canActivate: [noAuthGuard], data: { title: 'Connexion', description: 'Page de connexion Angular.' } },
  { path: 'inscription', component: Inscription, canActivate: [noAuthGuard], data: { title: 'Inscription', description: 'Création de compte Angular.' } },
  { path: 'a-propos', component: PageComponent, data: { title: 'À propos', description: 'Présentation du projet et de son fonctionnement.' } },
  { path: 'aide', component: PageComponent, data: { title: 'Aide', description: 'Réponses aux questions courantes.' } },
  { path: 'cgu', component: PageComponent, data: { title: 'Conditions générales d\'utilisation', description: 'Cadre légal et conditions d’utilisation.' } },
  { path: 'user/:id', component: ProfileComponent, canActivate: [authGuard], data: { title: 'Profil utilisateur', description: 'Tableau de bord du compte utilisateur.' } },
  { path: 'admin', component: AdminComponent, canActivate: [adminGuard], data: { title: 'Administration', description: 'Portail d\'administration.' } },
  { path: '**', redirectTo: 'catalogue' }
];