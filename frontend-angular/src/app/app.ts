import { Component } from '@angular/core';
import { CommonModule } from '@angular/common'; // Important pour le *ngIf
import { RouterModule } from '@angular/router'; // Important pour le routerLink

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './app.html',
  styleUrls: ['./app.css'] // (Vérifie si c'est .css ou .scss chez toi)
})
export class AppComponent {
  // Pour l'instant, on simule un utilisateur connecté pour voir le menu
  isLoggedIn: boolean = true; 
  userId: string = '123';
  unreadMessages: number = 3;

  onSearch(event: Event) {
    event.preventDefault();
    console.log('Recherche cliquée');
  }

  logout() {
    this.isLoggedIn = false;
    console.log('Déconnexion cliquée');
  }
}