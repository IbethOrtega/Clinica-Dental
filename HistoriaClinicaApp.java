import javax.swing.*;
import java.awt.*;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;

public class HistoriaClinicaApp {
    private static JFrame frame;
    private static JTextField nombreField, edadField, generoField, telefonoField;
    private static JTextArea areaPacientes;

    public static void main(String[] args) {
        // Crear la ventana principal
        frame = new JFrame("Sistema de Historia Clínica");
        frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        frame.setSize(600, 500);
        frame.setLocationRelativeTo(null); // Centrar la ventana
        frame.getContentPane().setBackground(new Color(245, 245, 245)); // Color de fondo

        // Crear panel principal
        JPanel panel = new JPanel();
        panel.setLayout(new GridBagLayout());
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.fill = GridBagConstraints.HORIZONTAL;
        gbc.insets = new Insets(10, 10, 10, 10); // Espaciado

        // Establecer estilo de fuente
        Font labelFont = new Font("Arial", Font.BOLD, 14);
        
        // Componentes de la interfaz
        gbc.gridx = 0; gbc.gridy = 0;
        panel.add(new JLabel("Nombre:"), gbc);
        nombreField = new JTextField(20);
        gbc.gridx = 1; gbc.gridy = 0;
        panel.add(nombreField, gbc);

        gbc.gridx = 0; gbc.gridy = 1;
        panel.add(new JLabel("Edad:"), gbc);
        edadField = new JTextField(20);
        gbc.gridx = 1; gbc.gridy = 1;
        panel.add(edadField, gbc);

        gbc.gridx = 0; gbc.gridy = 2;
        panel.add(new JLabel("Género:"), gbc);
        generoField = new JTextField(20);
        gbc.gridx = 1; gbc.gridy = 2;
        panel.add(generoField, gbc);

        gbc.gridx = 0; gbc.gridy = 3;
        panel.add(new JLabel("Teléfono:"), gbc);
        telefonoField = new JTextField(20);
        gbc.gridx = 1; gbc.gridy = 3;
        panel.add(telefonoField, gbc);

        JButton agregarButton = new JButton("Agregar Paciente");
        agregarButton.setBackground(new Color(70, 130, 180)); // Color del botón
        agregarButton.setForeground(Color.WHITE); // Color del texto
        agregarButton.setFocusPainted(false);
        agregarButton.addActionListener(new ActionListener() {
            @Override
            public void actionPerformed(ActionEvent e) {
                agregarPaciente();
            }
        });
        gbc.gridx = 0; gbc.gridy = 4; gbc.gridwidth = 2;
        panel.add(agregarButton, gbc);

        JButton verPacientesButton = new JButton("Ver Pacientes");
        verPacientesButton.setBackground(new Color(70, 130, 180));
        verPacientesButton.setForeground(Color.WHITE);
        verPacientesButton.setFocusPainted(false);
        verPacientesButton.addActionListener(new ActionListener() {
            @Override
            public void actionPerformed(ActionEvent e) {
                mostrarPacientes();
            }
        });
        gbc.gridy = 5; 
        panel.add(verPacientesButton, gbc);

        areaPacientes = new JTextArea();
        areaPacientes.setEditable(false);
        areaPacientes.setLineWrap(true);
        areaPacientes.setWrapStyleWord(true);
        areaPacientes.setFont(new Font("Arial", Font.PLAIN, 12));
        JScrollPane scrollPane = new JScrollPane(areaPacientes);
        scrollPane.setPreferredSize(new Dimension(550, 550));
        gbc.gridy = 6; 
        panel.add(scrollPane, gbc);

        // Establecer el panel en la ventana
        frame.add(panel);
        frame.setVisible(true);
    }

    private static void agregarPaciente() {
        String nombre = nombreField.getText();
        String edad = edadField.getText();
        String genero = generoField.getText();
        String telefono = telefonoField.getText();

        // Agregar paciente a la lista
        String pacienteInfo = String.format("Nombre: %s, Edad: %s, Género: %s, Teléfono: %s%n",
                nombre, edad, genero, telefono);
        areaPacientes.append(pacienteInfo);

        // Limpiar los campos de texto
        nombreField.setText("");
        edadField.setText("");
        generoField.setText("");
        telefonoField.setText("");
    }

    private static void mostrarPacientes() {
        // Muestra la lista de pacientes en el área de texto
        areaPacientes.append("\n--- Lista de Pacientes ---\n");
    }
}
