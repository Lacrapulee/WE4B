import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-admin-users',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './admin-users.component.html'
})
export class AdminUsersComponent {
  @Input() users: any[] = [];
  @Input() loading: boolean = false;
  @Output() refresh = new EventEmitter<void>();
  @Output() delete = new EventEmitter<any>();

  userSearchQuery: string = '';

  onRefresh() {
    this.refresh.emit();
  }

  onDelete(user: any) {
    this.delete.emit(user);
  }

  get filteredUsers(): any[] {
    if (!this.userSearchQuery.trim()) {
      return this.users;
    }
    const q = this.userSearchQuery.toLowerCase().trim();
    return this.users.filter(u => 
      (u.nom && u.nom.toLowerCase().includes(q)) ||
      (u.prenom && u.prenom.toLowerCase().includes(q)) ||
      (u.email && u.email.toLowerCase().includes(q)) ||
      (u.telephone && u.telephone.includes(q))
    );
  }
}
