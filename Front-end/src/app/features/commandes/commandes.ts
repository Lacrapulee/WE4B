import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';

@Component({
  selector: 'app-commandes',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './commandes.html',
  styleUrls: ['./commandes.css']
})
export class CommandesComponent implements OnInit {
  commandes: any[] = [];
  loading = true;

  constructor(private api: CatalogueApiService) {}

  ngOnInit(): void {
    this.api.getCommandes().subscribe({
      next: (data) => {
        this.commandes = data;
        this.loading = false;
      },
      error: (err) => {
        console.error('Erreur commandes:', err);
        this.loading = false;
      }
    });
  }
}
