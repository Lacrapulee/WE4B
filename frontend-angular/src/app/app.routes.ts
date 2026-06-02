import { Routes } from '@angular/router';
import { CatalogueComponent } from './features/catalogue/catalogue.component';
import { ItemDetailComponent } from './features/item-detail/item-detail.component';
import { PageComponent } from './features/page/page.component';

export const routes: Routes = [
  { path: '', pathMatch: 'full', redirectTo: 'catalogue' },
  { path: 'catalogue', component: CatalogueComponent },
  { path: 'item/:id', component: ItemDetailComponent },
  { path: 'vendre', component: PageComponent, data: { title: 'Vendre', description: 'Publiez une nouvelle annonce depuis Angular.' } },
  { path: 'favoris', component: PageComponent, data: { title: 'Favoris', description: 'Retrouvez ici les annonces enregistrées.' } },
  { path: 'mes-commandes', component: PageComponent, data: { title: 'Mes commandes', description: 'Suivi des commandes passées.' } },
  { path: 'messages', component: PageComponent, data: { title: 'Messages', description: 'Messagerie intégrée de l’application.' } },
  { path: 'login', component: PageComponent, data: { title: 'Connexion', description: 'Page de connexion Angular.' } },
  { path: 'inscription', component: PageComponent, data: { title: 'Inscription', description: 'Création de compte Angular.' } },
  { path: 'a-propos', component: PageComponent, data: { title: 'À propos', description: 'Présentation du projet et de son fonctionnement.' } },
  { path: 'aide', component: PageComponent, data: { title: 'Aide', description: 'Réponses aux questions courantes.' } },
  { path: 'cgu', component: PageComponent, data: { title: 'Conditions générales d\'utilisation', description: 'Cadre légal et conditions d’utilisation.' } },
  { path: 'user/:id', component: PageComponent, data: { title: 'Profil utilisateur', description: 'Tableau de bord du compte utilisateur.' } },
  { path: '**', redirectTo: 'catalogue' }
];