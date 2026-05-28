import { Routes } from '@angular/router';
import { CatalogueComponent } from './pages/catalogue/catalogue';

export const routes: Routes = [
  // Si l'URL est vide (accueil), on affiche directement le catalogue
  { path: '', component: CatalogueComponent }, 
  // Si l'URL est /catalogue, on l'affiche aussi
  { path: 'catalogue', component: CatalogueComponent }
];