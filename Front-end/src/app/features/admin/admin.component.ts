import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';
import { AdminDashboardComponent } from './components/admin-dashboard/admin-dashboard.component';
import { AdminUsersComponent } from './components/admin-users/admin-users.component';
import { AdminItemsComponent } from './components/admin-items/admin-items.component';

@Component({
  selector: 'app-admin',
  standalone: true,
  imports: [CommonModule, AdminDashboardComponent, AdminUsersComponent, AdminItemsComponent],
  templateUrl: './admin.component.html'
})
export class AdminComponent implements OnInit {
  activeTab: 'dashboard' | 'users' | 'items' = 'dashboard';
  
  // States
  users: any[] = [];
  items: any[] = [];
  loadingUsers: boolean = false;
  loadingItems: boolean = false;

  constructor(public api: CatalogueApiService) {}

  ngOnInit() {
    this.loadUsers();
    this.loadItems();
  }

  loadUsers() {
    this.loadingUsers = true;
    this.api.adminGetUsers().subscribe({
      next: (response) => {
        if (response.success) {
          this.users = response.result || [];
        } else {
          console.error("Erreur chargement utilisateurs:", response.error);
        }
        this.loadingUsers = false;
      },
      error: (err) => {
        console.error("Erreur réseau chargement utilisateurs:", err);
        this.loadingUsers = false;
      }
    });
  }

  loadItems() {
    this.loadingItems = true;
    this.api.adminGetItems().subscribe({
      next: (response) => {
        if (response.success) {
          this.items = response.result || [];
        } else {
          console.error("Erreur chargement annonces:", response.error);
        }
        this.loadingItems = false;
      },
      error: (err) => {
        console.error("Erreur réseau chargement annonces:", err);
        this.loadingItems = false;
      }
    });
  }

  deleteUser(user: any) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer (anonymiser) l'utilisateur "${user.prenom} ${user.nom}" ? Cette action est irréversible.`)) {
      this.api.adminDeleteUser(user.id).subscribe({
        next: (response) => {
          if (response.success) {
            alert("L'utilisateur a été anonymisé avec succès.");
            this.loadUsers();
          } else {
            alert(response.error || "Erreur lors de la suppression de l'utilisateur.");
          }
        },
        error: (err) => {
          console.error("Erreur réseau lors de la suppression de l'utilisateur:", err);
          alert("Une erreur réseau est survenue.");
        }
      });
    }
  }

  deleteItem(item: any) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer définitivement l'annonce "${item.titre}" ?`)) {
      this.api.deleteItem(item.id).subscribe({
        next: (response) => {
          if (response.success) {
            alert("L'annonce a été supprimée avec succès.");
            this.loadItems();
          } else {
            alert(response.error || "Erreur lors de la suppression de l'annonce.");
          }
        },
        error: (err) => {
          console.error("Erreur réseau lors de la suppression de l'annonce:", err);
          alert("Une erreur réseau est survenue.");
        }
      });
    }
  }
}
