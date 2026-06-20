import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-admin-items',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './admin-items.component.html'
})
export class AdminItemsComponent {
  @Input() items: any[] = [];
  @Input() loading: boolean = false;
  @Output() refresh = new EventEmitter<void>();
  @Output() delete = new EventEmitter<any>();

  itemSearchQuery: string = '';

  onRefresh() {
    this.refresh.emit();
  }

  onDelete(item: any) {
    this.delete.emit(item);
  }

  get filteredItems(): any[] {
    if (!this.itemSearchQuery.trim()) {
      return this.items;
    }
    const q = this.itemSearchQuery.toLowerCase().trim();
    return this.items.filter(i =>
      (i.titre && i.titre.toLowerCase().includes(q)) ||
      (i.vendeur_nom && i.vendeur_nom.toLowerCase().includes(q)) ||
      (i.vendeur_prenom && i.vendeur_prenom.toLowerCase().includes(q)) ||
      (i.categorie_nom && i.categorie_nom.toLowerCase().includes(q))
    );
  }
}
