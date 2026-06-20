import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CatalogueApiService } from '../../../../core/api/catalogue-api.service';

@Component({
  selector: 'app-admin-dashboard',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './admin-dashboard.component.html'
})
export class AdminDashboardComponent implements OnInit {
  loadingDashboard: boolean = false;
  dashboardError: string | null = null;
  dashboardSuccess: string | null = null;
  chartVersion: number = Date.now();

  constructor(private api: CatalogueApiService) {}

  ngOnInit() {
    this.refreshChart(false);
  }

  refreshChart(manual: boolean = true) {
    this.loadingDashboard = true;
    this.dashboardError = null;
    if (manual) {
      this.dashboardSuccess = null;
    }

    this.api.adminRunDashboard().subscribe({
      next: (response) => {
        if (response.success) {
          this.chartVersion = Date.now();
          if (manual) {
            this.dashboardSuccess = "Statistiques de l'API mises à jour avec succès.";
          }
        } else {
          this.dashboardError = response.error || "Une erreur inconnue est survenue.";
        }
        this.loadingDashboard = false;
      },
      error: (err) => {
        this.dashboardError = "Erreur réseau lors de la génération du graphique.";
        this.loadingDashboard = false;
      }
    });
  }
}
