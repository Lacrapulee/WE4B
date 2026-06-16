import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { CatalogueApiService } from '../../core/api/catalogue-api.service';
import { FormsModule } from '@angular/forms';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-paiement',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './paiement.component.html',
  styleUrls: ['./paiement.component.css']
})
export class PaiementComponent implements OnInit {
  articleId: number | null = null;
  loading = true;
  error: string | null = null;
  paymentData: any = null;
  
  buyerName: string = '';
  buyerEmail: string = '';
  
  isProcessing = false;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private http: HttpClient,
    public api: CatalogueApiService
  ) {}

  ngOnInit(): void {
    this.articleId = Number(this.route.snapshot.paramMap.get('id'));
    if (!this.articleId) {
      this.error = "ID de l'article manquant.";
      this.loading = false;
      return;
    }

    this.http.get<any>(`http://localhost:8000/api/paiement?id=${this.articleId}`, { withCredentials: true }).subscribe({
      next: (data) => {
        if (data && (data.statusCode === 200 || data.product)) {
          this.paymentData = data;
        } else {
          this.error = data?.errorMessage || data?.error || 'Erreur lors du chargement de la page de paiement.';
        }
        this.loading = false;
      },
      error: (err) => {
        console.error(err);
        if (err.status === 401) {
          this.router.navigate(['/login']);
        } else {
          this.error = err.error?.errorMessage || err.error?.error || err.message || "Une erreur s'est produite.";
        }
        this.loading = false;
      }
    });
  }

  onSubmitPayment() {
    if (!this.buyerName || !this.buyerEmail) {
      this.error = "Veuillez remplir votre nom et email.";
      return;
    }

    this.isProcessing = true;
    this.error = null;

    this.http.post<any>(`http://localhost:8000/api/paiement`, {
      article_id: this.articleId,
      buyer_name: this.buyerName,
      buyer_email: this.buyerEmail
    }, { withCredentials: true }).subscribe({
      next: (res) => {
        this.isProcessing = false;
        if (res?.success) {
          alert('Paiement réussi ! Redirection vers vos commandes.');
          this.router.navigate(['/mes-commandes']);
        } else {
          this.error = res?.error || "Erreur lors du paiement.";
        }
      },
      error: (err) => {
        this.isProcessing = false;
        this.error = err.error?.error || err.error?.errorMessage || "Erreur serveur.";
      }
    });
  }
}
