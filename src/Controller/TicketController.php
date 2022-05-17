<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Form\TicketType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TicketController extends AbstractController
{
    /**
     * @Route("/", name="tickets")
     */
    public function index(): Response
    {

        // Get all tickets where status is open
        $list = $this->getDoctrine()->getRepository(Ticket::class)->findBy(['status' => 'open']);

        // Init new ticket and create form data based on tickettype class
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);

        // Return list and form to view and render it
        return $this->render('tickets/index.html.twig', [
            'list' => $list,
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/tickets/create", name="create")
     */
    public function create(Request $request) 
    {

        // init new ticket object and create form based on object
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        // Set status to open
        $ticket->setStatus('open');

        // Set the dates
        $date_now = new \DateTime();
        $ticket->setDateCreated($date_now);
        $ticket->setDateUpdated($date_now);
        
        // When form submitted and valid, create message, render view with form data
        if($form->isSubmitted() && $form->isValid()){
            $existing = $this->getDoctrine()->getRepository(Ticket::class)->findOneBy(['title' => $ticket->getTitle()]);
            
            // Check for tickets with the same title, if found, display warning message
            if($existing) {
                $this->addFlash('warning','Ticket already exists');

                return $this->render('tickets/create.html.twig', [
                    'form' => $form->createView()
                ]);
            }

            // Since the data is valid, insert into database
            $em = $this->getDoctrine()->getManager();
            $em->persist($ticket);
            $em->flush();

            // Add success message and redirect back to the overview
            $this->addFlash('notice','Ticket added successfully!');
            return $this->redirectToRoute('tickets');
        }

        // Render basic create ticket view
        return $this->render('tickets/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/tickets/{id}", name="view")
     */
    public function view(Request $request, $id)
    {
        
        // Get ticket based on ticket id
        $data = $this->getDoctrine()->getRepository(Ticket::class)->find($id);

        // Return ticket data and render view
        return $this->render('tickets/view.html.twig', [
            'ticket' => $data
        ]);
    
    }

    /**
     * @Route("/tickets/close/{id}", name="close")
     */
    public function close(Request $request, $id) 
    {

        // Get ticket by ticket id
        $ticket = $this->getDoctrine()->getRepository(Ticket::class)->find($id);

        // Set status closed
        $ticket->setStatus('closed');

        // Update time updated
        $date_now = new \DateTime();
        $ticket->setDateUpdated($date_now);

        // Update database
        $em = $this->getDoctrine()->getManager();
        $em->persist($ticket);
        $em->flush();

        // Create message and redirect to ticket overview
        $this->addFlash('notice','Ticket closed successfully!');
        return $this->redirectToRoute('tickets');

    }

}
