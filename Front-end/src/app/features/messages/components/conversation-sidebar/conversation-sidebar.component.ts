import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-conversation-sidebar',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './conversation-sidebar.component.html',
  styleUrls: ['./conversation-sidebar.component.css']
})
export class ConversationSidebarComponent {
  @Input() conversations: any[] = [];
  @Input() selectedConversation: any = null;
  @Input() loading: boolean = true;
  @Output() conversationSelected = new EventEmitter<any>();

  selectConversation(conv: any) {
    this.conversationSelected.emit(conv);
  }
}
